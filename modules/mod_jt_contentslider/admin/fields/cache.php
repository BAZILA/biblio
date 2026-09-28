<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Log\Log;

/**
 * Custom form field for managing cached thumbnails
 */
class JFormFieldCache extends FormField
{
    /**
     * Redirect to clean URL to prevent resubmission
     */
    protected function redirectToCleanUrl()
    {
        try {
            $app = Factory::getApplication();
            if (!$app) {
                return;
            }
            
            $input = $app->input;
            
            // Build clean URL with current parameters but without POST data
            $url = 'index.php';
            $params = [];
            
            // Preserve important GET parameters
            $preserveParams = ['option', 'view', 'layout', 'tmpl', 'id', 'cid', 'client_id'];
            
            foreach ($preserveParams as $param) {
                $value = $input->get($param, '', 'string');
                if (!empty($value)) {
                    $params[] = $param . '=' . urlencode($value);
                }
            }
            
            if (!empty($params)) {
                $url .= '?' . implode('&', $params);
            }
            
            // Perform redirect
            $app->redirect($url);
            
        } catch (Exception $e) {
            // If redirect fails, just continue normally
            return;
        }
    }

    /**
     * The form field type.
     */
    protected $type = 'Cache';

    /**
     * Method to get the field input markup.
     */
    protected function getInput()
    {
        // Handle deletion request first
        $this->handleDeletion();
        
        // Get cache statistics
        $stats = $this->getCacheStats();
        
        // Generate unique token
        $token = Session::getFormToken();
        
        $html = [];
        
        // Cache information
        $html[] = '<div class="alert alert-info" style="margin-bottom: 10px; padding: 8px 12px;">';
        $html[] = '<strong><i class="icon-info"></i> ' . Text::_('MOD_JTCS_CACHE_INFO') . '</strong><br>';
        
        if ($stats['count'] > 0) {
            $html[] = Text::sprintf('MOD_JTCS_CACHE_FILES_COUNT', $stats['count']) . ' (' . $this->formatBytes($stats['size']) . ')';
        } else {
            $html[] = Text::_('MOD_JTCS_NO_CACHE_FILES');
        }
        $html[] = '</div>';

        // Delete button and form
        if ($stats['count'] > 0) {
            $html[] = '<div class="cache-delete-section">';
            $html[] = '<form method="post" action="" onsubmit="return confirmCacheDelete();" style="display: inline-block;">';
            
            // Preserve current URL parameters
            $html[] = $this->getHiddenFields();
            
            // Cache deletion fields
            $html[] = '<input type="hidden" name="delete_cache_thumbs" value="1" />';
            $html[] = '<input type="hidden" name="' . $this->safeHtml($token) . '" value="1" />';
            
            $html[] = '<button type="submit" class="btn btn-danger btn-small">';
            $html[] = '<i class="icon-delete"></i> ' . Text::_('MOD_JTCS_THUMBNAIL_DELETE_LABEL');
            $html[] = '</button>';
            $html[] = '</form>';
            $html[] = '</div>';
        } else {
            $html[] = '<div class="alert alert-warning" style="padding: 8px 12px;">';
            $html[] = '<i class="icon-info-circle"></i> ' . Text::_('MOD_JTCS_NO_FILES_TO_DELETE');
            $html[] = '</div>';
        }

        // Add JavaScript for confirmation
        $html[] = $this->getConfirmationScript($stats['count']);

        return implode("\n", $html);
    }

    /**
     * Handle cache deletion request
     */
    protected function handleDeletion()
    {
        try {
            $app = Factory::getApplication();
            if (!$app) {
                return;
            }
            
            $input = $app->input;
            
            // Check if this is a deletion request
            if (!$input->get('delete_cache_thumbs', 0, 'int')) {
                return;
            }
            
            // Verify CSRF token
            if (!Session::checkToken()) {
                $app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
                $this->redirectToCleanUrl();
                return;
            }
            
            // Check user permissions
            $user = Factory::getUser();
            if (!($user->authorise('core.admin') || $user->authorise('core.manage', 'com_modules'))) {
                $app->enqueueMessage(Text::_('MOD_JTCS_ERROR_NO_PERMISSION'), 'error');
                $this->redirectToCleanUrl();
                return;
            }
            
            // Perform deletion
            $result = $this->deleteCacheFiles();
            
            if ($result['success']) {
                if ($result['deleted'] > 0) {
                    $message = Text::sprintf('MOD_JTCS_CACHE_DELETED_SUCCESS', $result['deleted'], $result['total']);
                    $app->enqueueMessage($message, 'success');
                    
                    // Log the action
                    if (class_exists('Joomla\CMS\Log\Log')) {
                        Log::add(
                            sprintf('Thumbnail cache cleared by user %d: %d files deleted', $user->id, $result['deleted']),
                            Log::INFO,
                            'mod_jt_contentslider'
                        );
                    }
                } else {
                    $app->enqueueMessage(Text::_('MOD_JTCS_NO_FILES_TO_DELETE'), 'info');
                }
            } else {
                $app->enqueueMessage($result['message'], 'error');
            }
            
            // Redirect to prevent resubmission on page refresh
            $this->redirectToCleanUrl();
            
        } catch (Exception $e) {
            // Silently handle any errors to prevent breaking the form
            if (class_exists('Joomla\CMS\Log\Log')) {
                Log::add('Cache deletion error: ' . $e->getMessage(), Log::ERROR, 'mod_jt_contentslider');
            }
        }
    }

    /**
     * Delete cache files
     */
    protected function deleteCacheFiles()
    {
        $cacheDir = JPATH_SITE . '/cache/mod_jt_contentslider';
        
        // Check if cache directory exists
        if (!is_dir($cacheDir)) {
            return [
                'success' => false,
                'message' => Text::_('MOD_JTCS_ERROR_CACHE_DIR_NOT_FOUND'),
                'deleted' => 0,
                'total' => 0
            ];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $pattern = $cacheDir . '/*.{' . implode(',', $allowedExtensions) . '}';
        $files = glob($pattern, GLOB_BRACE);
        
        if (!$files) {
            return [
                'success' => true,
                'message' => Text::_('MOD_JTCS_NO_FILES_TO_DELETE'),
                'deleted' => 0,
                'total' => 0
            ];
        }

        $totalFiles = count($files);
        $deletedCount = 0;
        $errors = [];

        foreach ($files as $file) {
            // Security check - ensure file is within cache directory
            $realCacheDir = realpath($cacheDir);
            $realFileDir = realpath(dirname($file));
            
            if (!$realCacheDir || !$realFileDir || $realFileDir !== $realCacheDir) {
                continue;
            }

            if (is_file($file) && is_writable($file)) {
                if (unlink($file)) {
                    $deletedCount++;
                } else {
                    $errors[] = basename($file);
                }
            }
        }

        return [
            'success' => true,
            'deleted' => $deletedCount,
            'total' => $totalFiles,
            'errors' => $errors
        ];
    }

    /**
     * Safe htmlspecialchars that handles non-string values
     * 
     * @param mixed $value Value to escape
     * @param string $default Default value if input is invalid
     * @return string Escaped string
     */
    protected function safeHtml($value, $default = '')
    {
        if (is_array($value) || is_object($value)) {
            return $default;
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get hidden fields to preserve form state
     */
    protected function getHiddenFields()
    {
        $html = [];
        
        try {
            $app = Factory::getApplication();
            if (!$app) {
                return '';
            }
            
            $input = $app->input;
            
            // Fields to preserve
            $preserveFields = ['option', 'view', 'layout', 'tmpl', 'id', 'cid', 'client_id'];
            
            foreach ($preserveFields as $field) {
                $value = $input->get($field, '', 'string');
                if (!empty($value)) {
                    $html[] = '<input type="hidden" name="' . $this->safeHtml($field) . '" value="' . $this->safeHtml($value) . '" />';
                }
            }
            
            // Preserve jform data with proper array handling
            $jform = $input->get('jform', [], 'array');
            if (!empty($jform) && is_array($jform)) {
                $html[] = $this->generateHiddenFieldsFromArray($jform, 'jform');
            }
            
        } catch (Exception $e) {
            // Return empty string if there's any error
            return '';
        }
        
        return implode("\n", $html);
    }

    /**
     * Recursively generate hidden fields from array
     * 
     * @param array $data Data array
     * @param string $prefix Field name prefix
     * @return string Hidden fields HTML
     */
    protected function generateHiddenFieldsFromArray($data, $prefix = '')
    {
        $html = [];
        
        foreach ($data as $key => $value) {
            // Skip the delete flag
            if ($key === 'delete_cache_thumbs') {
                continue;
            }
            
            $fieldName = $prefix ? $prefix . '[' . $this->safeHtml($key) . ']' : $this->safeHtml($key);
            
            if (is_array($value)) {
                // Recursively handle nested arrays
                $html[] = $this->generateHiddenFieldsFromArray($value, $fieldName);
            } elseif (is_scalar($value) || $value === null) {
                // Only process scalar values (string, int, float, bool) or null
                $value = $value === null ? '' : (string)$value;
                $html[] = '<input type="hidden" name="' . $fieldName . '" value="' . $this->safeHtml($value) . '" />';
            }
            // Skip objects and resources
        }
        
        return implode("\n", $html);
    }

    /**
     * Get cache directory statistics
     */
    protected function getCacheStats()
    {
        $cacheDir = JPATH_SITE . '/cache/mod_jt_contentslider';
        
        if (!is_dir($cacheDir)) {
            return ['count' => 0, 'size' => 0];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $pattern = $cacheDir . '/*.{' . implode(',', $allowedExtensions) . '}';
        $files = glob($pattern, GLOB_BRACE);
        
        if (!$files) {
            return ['count' => 0, 'size' => 0];
        }

        $totalSize = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                if ($size !== false) {
                    $totalSize += $size;
                }
            }
        }

        return [
            'count' => count($files),
            'size' => $totalSize
        ];
    }

    /**
     * Format bytes into human readable format
     */
    protected function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($bytes, 1024);
        
        return round(pow(1024, $base - floor($base)), 1) . ' ' . $units[floor($base)];
    }

    /**
     * Get JavaScript confirmation dialog
     */
    protected function getConfirmationScript($fileCount)
    {
        if ($fileCount == 0) {
            return '';
        }
        
        $message = Text::sprintf('MOD_JTCS_CONFIRM_DELETE_CACHE', $fileCount);
        
        return '
        <script type="text/javascript">
            function confirmCacheDelete() {
                return confirm("' . addslashes($message) . '");
            }
        </script>';
    }
}
?>