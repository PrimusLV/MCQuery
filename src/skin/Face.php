<?php
namespace skin;

class Face {
    
    protected $image = "";
    
    public function __construct($skin) {
        $this->image = $skin;
    }
    
    public function getFace($size = 32) {
        $im = imagecreatefromstring($this->skin);
        $av = imagecreatetruecolor($size, $size);
        $x = array('f' => 8, 'l' => 16, 'r' => 0, 'b' => 24);
        imagecopyresized($av, $im, 0, 0, $x[$view], 8, $size, $size, 8, 8);         // Face
        imagecolortransparent($im, imagecolorat($im, 63, 0));                       // Black Hat Issue
        imagecopyresized($av, $im, 0, 0, $x[$view] + 32, 8, $size, $size, 8, 8);    // Accessories
    }
    
}