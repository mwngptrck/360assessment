<?php
/**
 * TCPDF Stub - Replace with full TCPDF library
 * Download TCPDF from: https://github.com/tecnickcom/TCPDF
 * 
 * This is a minimal stub to allow the plugin to load.
 * For full PDF functionality, download and extract TCPDF library here.
 */

if (!class_exists('TCPDF')) {
    
    // Define constants
    if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
    if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
    if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
    
    class TCPDF {
        
        public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
            // Constructor stub
        }
        
        public function SetCreator($creator) {}
        public function SetAuthor($author) {}
        public function SetTitle($title) {}
        public function SetSubject($subject) {}
        public function setPrintHeader($val) {}
        public function setPrintFooter($val) {}
        public function SetMargins($left, $top, $right) {}
        public function SetAutoPageBreak($auto, $margin = 0) {}
        public function AddPage() {}
        public function SetFont($family, $style = '', $size = 0) {}
        public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {}
        
        public function Output($name = 'doc.pdf', $dest = 'I') {
            // For download functionality
            if ($dest === 'D') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $name . '"');
                echo "PDF generation requires full TCPDF library. Please install TCPDF.";
                return;
            }
            return "PDF generation requires full TCPDF library. Please install TCPDF.";
        }
    }
}