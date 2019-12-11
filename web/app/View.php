<?php 
namespace App;

use App\Config;

class View
{
    const HEADER_FILE = 'app/Views/header.view.php';
    const FOOTER_FILE = 'app/Views/footer.view.php';

    private $data = array();
    private $file = false;

    /**
     * Create a new view from the specified template name.
     * e.g. 'Articles/index'
     */
    public function __construct($template)
    {
        $file = 'app/Views/' . $template . '.view.php';
        
        if (file_exists($file)) 
            $this->file = $file;
        else 
            $this->file = 'app/Views/Pages/error.php';
    }

    /**
     * Add the variables that need to be displayed on the view.
     */
    public function assign($variable, $value)
    {
        $this->data[$variable] = $value;
    }
    
    /**
     * Display the view with the header and footer.
     */
    public function show()
    {
        extract($this->data); 
        include(View::HEADER_FILE);
        include($this->file);
        include(View::FOOTER_FILE);
    }
}