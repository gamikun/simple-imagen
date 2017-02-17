<?php

/*
    Actualización: 18-Ago-2015
    ---------------------------
        - A la función cambiarTamano, se le agregó el parámetro
          $keep_alpha, que sirve para que al redimensionar mantenga
          las transparencias originales. El funcionamiento anterior
          no se movió.
        - Agregada la función "guardarPNG"... el nombre lo dice todo.

    Por su atención, Muchas Gracias :)
    Atte. Gamaliel Espinoza Macedo (gespinoza@edesarrollos.com)
*/

namespace edesarrollos\simpleimagen;

class Imagen
{
    private $imagen;
    private $ancho;
    private $alto;
    private $tipo;
    
    const JPEG  = 1;
    const GIF   = 2;
    
    public function Imagen( &$imagen = null )
    {
        if( !is_null($imagen) )
            $this->ponerImagen( $imagen );
    }
    
    /**
     * Carga la imagen especificada.
     * @param $archivo Nombre del archivo de imagen.
     * @param $mime (Obsoleto)
     */
    public function cargar($archivo, $mime=null) {
        // Obtener las medidas de la imagen
        if( file_exists($archivo) ) {
            $this->tipo = exif_imagetype($archivo);
            if( !($this->tipo === false) ) {
                if( $this->tipo == IMAGETYPE_JPEG ) {
                    $this->imagen = imagecreatefromjpeg($archivo);
                }
                elseif( $this->tipo == IMAGETYPE_GIF ) {
                    $this->imagen = imagecreatefromgif($archivo);
                }
                elseif( $this->tipo == IMAGETYPE_PNG ) {
                    $this->imagen = imagecreatefrompng($archivo);
                }
                // obtener el ancho y alto
                if( $this->imagen ) {
                    $this->ancho = imagesx($this->imagen);
                    $this->alto = imagesy($this->imagen);
                }
            }
        }
    }
    
    /**
     * Próximamente obsoleto
     */
    public function ponerImagen(&$imagen)
    {
        $this->imagen =& $imagen;
        $this->ancho = imagesx($this->imagen);
        $this->alto = imagesy($this->imagen);
    }
    
    public function esValida()
    {
        return $this->imagen;
    }

    public function guardar($archivo, $calidad = 500, $liberar = false)
    {
        imagejpeg( $this->imagen, $archivo, $calidad );
            
        // Decidir si se libera la memoria
        if( $liberar )
            $this->liberar();

    }

    public function guardarPNG($archivo)
    {
        imagepng($this->imagen, $archivo);
    }
    
    function liberar()
    {
        imagedestroy( $this->imagen );
    }
    
    public function &recortarCuadro($tamano=null)
    {
        if( $this->ancho > $this->alto )
            $tam = $this->alto;
        elseif( $this->alto > $this->ancho )
            $tam = $this->ancho;
        else
            $tam = $this->alto;
            
        // Obtener las posiciones
        $x = ($this->ancho/2) - ($tam/2 );
        $y = ($this->alto/2 ) - ($tam/2 );

        // Se define el tamaño al que se forzará
        if( is_null($tamano) || !is_numeric($tamano) || $tamano == 0 )
            $tamano = $tam;
        
        // Crear la imagen recortada
        $img = imagecreatetruecolor( $tamano, $tamano );
        if( $this->tipo == IMAGETYPE_GIF || $this->tipo == IMAGETYPE_PNG ) {
            $col = imagecolorallocate($img,255,255,255);
            imagefill($img,0,0,$col);
        }
        imagecopyresampled( $img, $this->imagen, 0, 0, $x, $y, $tamano, $tamano, $tam, $tam );      
        
        $imag = new Imagen( $img );
        $imag->tipo = $this->tipo;
        return $imag;
    }
    
    public function &cambiarTamano($w,$h=0, $m_prop = true, $keep_alpha = false)
    {
        if( $m_prop ):
            if( ($this->ancho >= $this->alto) || ($w > 0 && $h == 0) ):
                $prop = $this->alto / $this->ancho;
                $ancho = $w;
                $alto = $ancho * $prop;
            else:
                $prop = $this->ancho / $this->alto;
                $alto = $h;
                $ancho = $alto * $prop;
            endif;
        else:
            list( $ancho, $alto ) = array( $w, $h );
        endif;
        
        // Crear la nueva imagen
        $img = imagecreatetruecolor($ancho,$alto);
        if($keep_alpha === false) {
            if( $this->tipo == IMAGETYPE_GIF || $this->tipo == IMAGETYPE_PNG ) {
                $col = imagecolorallocate($img,255,255,255);
                imagefill($img,0,0,$col);
            }
        } else {
            imagesavealpha($img, true);
            imagealphablending( $img, false );
        }
        
        imagecopyresampled( $img, $this->imagen, 0, 0, 0, 0, $ancho,
            $alto, $this->ancho, $this->alto);

        $imag = new Imagen( $img );
        return $imag;
    }
    
    public function __get($k)
    {
        if( $k == 'ancho' || $k ==  'width' )
            return $this->ancho;
        elseif( $k == 'alto' || $k == 'height' )
            return $this->alto;
    }

    /**
     * Do not remove this function, it has the purpose
     * of testing, also do not use it for anything. 
     * @return ni una ñonga
     */
    public static function testImage() {
        $img = new Imagen();
        $img->cargar("/Users/gama/Desktop/stat.png");
        $ni = $img->cambiarTamano(100, null, true, false);
        $ni->guardarPNG("fuck.png");
    }
}