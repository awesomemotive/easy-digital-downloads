<?php
/**
 * PDF MultiCell Table Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  FPDF
 * @since       1.1.3.2
*/

class edd_pdf extends FPDF {

    var $widths;
    var $aligns;

    function SetWidths( $w ) {
        $this->widths = $w;
    }

    function SetAligns( $a ) {
        $this->aligns = $a;
    }

    function Row( $data ) {
        $nb = 0;
        for ( $i = 0; $i < count( $data ); $i++ )
            $nb = max( $nb, $this->NbLines( $this->widths[$i], $data[$i] ) );
            $h = 5 * $nb;
        $this->CheckPageBreak($h);
        for ( $i = 0; $i < count( $data ); $i++ ) {
            $w = $this->widths[$i];
            $a = isset( $this->aligns[$i] ) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect( $x, $y, $w, $h );
            $this->MultiCell( $w, 5 , $data[$i], 0, $a);
            $this->SetXY( $x + $w , $y );
        }
        $this->Ln( $h );
    }

    function CheckPageBreak( $h ) {
        if ( $this->GetY() + $h > $this->PageBreakTrigger ) {
            $this->AddPage( $this->CurOrientation );
        }
    }

    function NbLines( $w, $txt ) {
        $cw = &$this->CurrentFont['cw'];

        if ( $w == 0 ) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        
        $wmax = ( $w - 2 * $this->cMargin ) * 1000 / $this->FontSize;
        $s = str_replace( "\r", '', $txt );
        $nb = strlen( $s );
        
        if ( $nb > 0 and $s[ $nb - 1 ] == "\n") {
            $nb--;
        }
        
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        
        while ( $i < $nb ) :
            $c = $s[$i];
        
            if ( $c == "\n" ) {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
                continue;
            }
        
            if ( $c == ' ' ) {
                $sep = $i;
            }
        
            $l += $cw[ $c ];
        
            if ($l > $wmax) {
                if ( $sep == -1 ) {
                    if ( $i == $j )
                        $i++;
                }
                else
                    $i = $sep +1;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
            }
            else
                $i++;
        endwhile;
        return $nl;
    }

}

function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['V']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
////////////////////////////////////

class PDF_HTML extends edd_pdf
{
//variables of html parser
var $B;
var $I;
var $U;
var $HREF;
var $fontList;
var $issetfont;
var $issetcolor;

function PDF_HTML($orientation='P', $unit='mm', $format='A4')
{
    //Call parent constructor
    $this->FPDF($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
    $this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
    $this->issetfont=false;
    $this->issetcolor=false;
}

function WriteHTML($html)
{
    //HTML parser
    $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
    $html=str_replace("\n",' ',$html); //remplace retour à la ligne par un espace
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,stripslashes(txtentities($e)));
        }
        else
        {
            //Tag
            if($e[0]=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                //Extract attributes
                $a2=explode(' ',$e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                {
                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                }
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function OpenTag($tag, $attr)
{
    //Opening tag
    switch($tag){
        case 'STRONG':
            $this->SetStyle('B',true);
            break;
        case 'EM':
            $this->SetStyle('I',true);
            break;
        case 'B':
        case 'I':
        case 'U':
            $this->SetStyle($tag,true);
            break;
        case 'A':
            $this->HREF=$attr['HREF'];
            break;
        case 'IMG':
            if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                if(!isset($attr['WIDTH']))
                    $attr['WIDTH'] = 0;
                if(!isset($attr['HEIGHT']))
                    $attr['HEIGHT'] = 0;
                $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
            }
            break;
        case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5);
            break;
        case 'P':
            $this->Ln(10);
            break;
        case 'FONT':
            if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                $coul=hex2dec($attr['COLOR']);
                $this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
                $this->issetcolor=true;
            }
            if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                $this->SetFont(strtolower($attr['FACE']));
                $this->issetfont=true;
            }
            break;
    }
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='FONT'){
        if ($this->issetcolor==true) {
            $this->SetTextColor(0);
        }
        if ($this->issetfont) {
            $this->SetFont('arial');
            $this->issetfont=false;
        }
    }
}

function SetStyle($tag, $enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
    {
        if($this->$s>0)
            $style.=$s;
    }
    $this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}

}//end of class