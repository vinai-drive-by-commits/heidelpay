<?php

class Mage_Heidelpay_Block_Redirect extends Mage_Core_Block_Abstract
{
    public function setIframeHtml($url, $height)
    {
        $html = '<iframe src="' . $url . '" width="100%" height="' . $height . '" name="hp_iframe" frameborder="0">';
        $html .= '<p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
      Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis
      aufrufen: <a href="' . $url . '">Payment information</a></p>';
        $html .= '</iframe>';

        return $html;
    }
}
