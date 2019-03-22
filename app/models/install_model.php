<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Notices Model 
*
* Set and retrieve the notices and errors that appear at the top of the control panel
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Electric Publisher

*/
class Install_Model extends CI_Model {
    
    private $config = array();
    private $installdb = '';

    function __construct() {
        parent::__construct();
    }

    function validate_creds()
    {
        $valid_mysql = FALSE;
        $this->config = array(
            'hostname' => $this->input->post('db_host'),
            'username' => $this->input->post('db_user'),
            'password' => $this->input->post('db_pass'),
            'database' => $this->input->post('db_name'),
            'dbdriver' => "mysqli",
            'dbprefix' => "",
            'pconnect' => FALSE,
            'db_debug' => TRUE,
            'cache_on' => FALSE,
            'cachedir' => "",
            'char_set' => "utf8",
            'dbcollat' => "utf8_general_ci"
        );
        $this->installdb = $this->load->database($this->config);
        if ($this->installdb) {
            $valid_mysql = TRUE;
        }
        return $valid_mysql;
    }

    function run_setup_queries()
    {
        $structure = read_file(APPPATH . 'updates/install.php');
        $structure = str_replace('<?php if (!defined(\'BASEPATH\')) exit(\'No direct script access allowed\'); ?>','',$structure);
        
        // break into newlines
        $structure = explode("\n",$structure);
        
        // run mysql queries
        $query = "";
        $querycount = 0;
        
        foreach ($structure as $sql_line)
        {
            if (trim($sql_line) != "" and substr($sql_line,0,2) != "--")
            {
                $query .= $sql_line;
                if (substr(trim($query), -1, 1) == ";")
                {
                    // this query is finished, execute it
                    if ($this->installdb->query($query)) {
                        $query = "";
                        $querycount++;
                    } else {
                        show_error('There was a critical error importing the initial database structure.  Please contact support.<br /><br />Query:<br /><br />' . $query);
                        die();
                    }
                }
            }
        }
        // update settings
        $this->installdb->query(
            'UPDATE `settings` SET `setting_value`="' 
            . $this->input->post('site_name') 
            . '" WHERE `setting_name`="site_name" or `setting_name`="email_name"'
        );
        $this->installdb->query(
            'UPDATE `settings` SET `setting_value`="' 
            . $this->input->post('site_email') 
            . '" WHERE `setting_name`="site_email"'
        );
    }
}