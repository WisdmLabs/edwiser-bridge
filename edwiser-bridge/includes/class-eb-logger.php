<?php

/**
 * Allows log files to be written to for debugging purposes.
 *
 * @link       https://edwiser.org
 * @since      1.0.0
 *
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

namespace app\wisdmlabs\edwiserBridge;

class EBLogger
{
    /**
     * @var array Stores open file _handles.
     */
    //private $handles;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of this plugin.
     */
    private $version;

    /**
     * @var EB_Course_Manager The single instance of the class
     *
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Main EBLogger Instance.
     *
     * Ensures only one instance of EBLogger is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     *
     * @see EBLogger()
     *
     * @return EBLogger - Main instance
     */
    public static function instance($plugin_name, $version)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($plugin_name, $version);
        }

        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since   1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since   1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'eb-textdomain'), '1.0.0');
    }

    /**
     * Constructor for the logger.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->_handles = array();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        foreach ($this->_handles as $handle) {
            @fclose(escapeshellarg($handle));
        }
    }

    /**
     * Open log file for writing.
     *
     * @param mixed $handle
     *
     * @return bool success
     */
    private function open($handle)
    {
        if (isset($this->_handles[$handle])) {
            return true;
        }

        $this->_handles[$handle] = @fopen(wdmLogFilePath($handle), 'a');

        if ($this->_handles[$handle]) {
            return true;
        }

        return false;
    }

    /**
     * Add a log entry to chosen file.
     *
     * @param string $handle
     * @param string $message
     */
    public function add($handle, $message)
    {
        if ($this->open($handle) && is_resource($this->_handles[$handle])) {
            $time = date_i18n('m-d-Y @ H:i:s -'); // Grab Time
            @fwrite($this->_handles[$handle], $time.' '.$message."\n");
        }
    }

    /**
     * Clear entries from chosen file.
     *
     * @param mixed $handle
     */
    public function clear($handle)
    {
        if ($this->open($handle) && is_resource($this->_handles[$handle])) {
            @ftruncate($this->_handles[$handle], 0);
        }
    }
}
