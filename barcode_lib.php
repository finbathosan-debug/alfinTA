<?php
// Library sederhana untuk generate barcode PNG menggunakan GD
// (tanpa dependency eksternal, hanya Code128)
function generate_barcode_png($text, $filename) {
    // Dummy: buat PNG kosong dengan tulisan barcode saja (bisa diganti library barcode asli)
    $im = imagecreatetruecolor(120, 40);
    $white = imagecolorallocate($im, 255,255,255);
    $black = imagecolorallocate($im, 0,0,0);
    imagefilledrectangle($im, 0,0,120,40, $white);
    imagestring($im, 5, 10, 12, $text, $black);
    imagepng($im, $filename);
    imagedestroy($im);
}
