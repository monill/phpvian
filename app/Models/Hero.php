<?php

namespace PHPvian\Models;

class Hero
{
    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        // Verifica se o parâmetro $pct está definido e está entre 0 e 100
        if (!isset($pct) || $pct < 0 || $pct > 100) {
            return false;
        }

        $pct /= 100; // Converte para uma faixa de 0 a 1

        // Obtém a largura e altura da imagem de origem
        $w = imagesx($src_im);
        $h = imagesy($src_im);

        // Desliga o blending de alpha
        imagealphablending($src_im, false);

        // Encontra o pixel mais opaco na imagem de origem
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                $minalpha = min($minalpha, $alpha);
            }
        }

        // Percorre os pixels da imagem de origem e ajusta a transparência
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;

                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }

                $alphacolorxy = imagecolorallocatealpha(
                    $src_im,
                    ($colorxy >> 16) & 0xFF,
                    ($colorxy >> 8) & 0xFF,
                    $colorxy & 0xFF,
                    $alpha
                );

                imagesetpixel($src_im, $x, $y, $alphacolorxy);
            }
        }

        // Realiza a cópia da imagem de origem na imagem de destino
        return imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }


}