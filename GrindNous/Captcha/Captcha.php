<?php
/**
 * Copyright (c) 2013 https://github.com/circunspecter
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace GrindNous\Captcha;

class Captcha
{
	public static $sessionNamespace = 'gncaptcha';

	protected $questionType = array(
		'normal',
		'ghost',
		'strikethrough',
		'notStrikethrough'
	);

	protected $properties = array(
		'name' => 'default',
		'questionType' => 'normal',
		'ltr' => TRUE,
        'minTimeLapse' => 10,
		'maxTimeLapse' => 600,
        'width'   => 140,
        'height'   => 60,
        'imageType' => 'png',
        'bgColor' => '#fff',
        'textColor' => '#333',
        'ghostText' => TRUE,
        'ghostTextColor' => '#333',
        'lines' => TRUE,
        'lineColor' => '#333',
        'lineWidth' => 2,
        'circles' => TRUE,
        'circleColor' => '#333',
        'length' => 4,
        'fontFile' => 'ttf_molten/molten.ttf',
        'fontSize' => 22,
        'letterSpacing' => 0
    );

	public function __construct(array $properties = array())
	{
		if(session_id() == "") session_start();

        $this->properties = array_merge($this->properties, $properties);
	}

	/**
	 * Returns the specified property and optionally sets its value.
	 * @return int|string
	 */
	public function prop($name, $value = NULL)
	{
		if(array_key_exists($name, $this->properties))
		{
			if($value)
			{
				$this->properties[$name] = $value;
			}

			return $this->properties[$name];
		}

		return NULL;
	}

	/**
	 * @return resource
	 */
	protected function createImage()
	{
		$frontString = $this->getString();
		$ghostString = $this->getString();
		$font = __DIR__.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR.$this->properties['fontFile'];

		$image = imagecreatetruecolor($this->properties['width'], $this->properties['height']);

		$bgColor = $this->parseColor($this->properties['bgColor'], array(255, 255, 255));
		imagefill($image, 0, 0, imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]));

		// Build chars array with properties
		$chars = array();
		$chars_width = 0;
		$paddingBottom = 5;
		for($i = 0; $i < strlen($frontString); $i++)
		{  
			$angle = mt_rand(-35, 35);

			$cBox = imagettfbbox($this->properties['fontSize'], $angle, $font, $frontString[$i]);

		    $width = max(array($cBox[2], $cBox[4])) - min(array($cBox[0], $cBox[6]));
		    $height = max(array($cBox[1], $cBox[3])) - min(array($cBox[5], $cBox[7]));

		    $y = ($height >= $this->properties['height'] - $paddingBottom)
				? $height + floor(($this->properties['height'] - $height) / 2)
				: mt_rand($height, $this->properties['height'] - $paddingBottom) ;

			$chars[] = array(
				'char' => $frontString[$i],
				'angle' => $angle,
				'width' => $width,
				'height' => $height,
				'x' => $chars_width + abs($cBox[6]),
				'y' => $y
			);
			$chars_width += $width + $this->properties['letterSpacing'];
		}

		// Space to center front text
		$centeringFront = floor(($this->properties['width'] - $chars_width) / 2);

		$textColor = $this->parseColor($this->properties['textColor'], array(51, 51, 51));
		$textColor = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
		
		if(
			$this->properties['ghostText'] === TRUE
			OR
			$this->properties['questionType'] === 'ghost'
		)
		{
			// Build ghost chars array with properties
			$ghostChars = array();
			$chars_width = 0;
			for($i = 0; $i < strlen($ghostString); $i++)
			{  
				$angle = mt_rand(-35, 35);
				$cBox = imagettfbbox($this->properties['fontSize'] * 1.2, $angle, $font, $ghostString[$i]);
				$width = max(array($cBox[2], $cBox[4])) - min(array($cBox[0], $cBox[6]));
		    	$height = max(array($cBox[1], $cBox[3])) - min(array($cBox[5], $cBox[7]));
				$ghostChars[] = array(
					'char' => $ghostString[$i],
					'angle' => $angle,
					'x' => $chars_width + abs($cBox[6]),
					'y' => $height + floor(($this->properties['height'] - $height) / 2)
				);
				$chars_width += $width + $this->properties['letterSpacing'];
			}

			// Space to center ghost text
			$centeringGhost = floor(($this->properties['width'] - $chars_width) / 2);

			$ghostTextColor = $this->parseColor($this->properties['ghostTextColor'], array(51, 51, 51));
			$ghostTextColor = imagecolorallocatealpha($image, $ghostTextColor[0], $ghostTextColor[1], $ghostTextColor[2], mt_rand(60, 70));
		}

		if(
			$this->properties['lines'] === TRUE
			OR
			$this->properties['questionType'] === 'strikethrough'
			OR
			$this->properties['questionType'] === 'notStrikethrough'
		)
		{
			$strikethroughMax = ($this->properties['questionType'] === 'notStrikethrough')
								? mt_rand(1, $this->properties['length'] - 1)
								: mt_rand(1, $this->properties['length']) ;

			$strikethroughChars = array_rand($chars, $strikethroughMax);
			if( !is_array($strikethroughChars)) $strikethroughChars = array($strikethroughChars);

			$lineColor = $this->parseColor($this->properties['lineColor'], array(51, 51, 51));
			$lineColor = imagecolorallocate($image, $lineColor[0], $lineColor[1], $lineColor[2]);
		}

		if($this->properties['circles'] === TRUE)
		{
			$circleColorRGB = $this->parseColor($this->properties['circleColor'], array(51, 51, 51));
		}

		$code = '';

		// Write chars and effects
		foreach($chars as $index => $char)
		{
			// Circles
			if($this->properties['circles'] === TRUE)
			{
				$eSize = mt_rand(5, $this->properties['height']);
				$eColor = imagecolorallocatealpha($image, $circleColorRGB[0], $circleColorRGB[1], $circleColorRGB[2], mt_rand(60, 100));
				imagefilledellipse($image, mt_rand(0, $this->properties['width']), mt_rand(0, $this->properties['height']), $eSize, $eSize, $eColor);
			}

			// Ghost chars
			if(
				$this->properties['ghostText'] === TRUE
				OR
				$this->properties['questionType'] === 'ghost'
			)
			{
				imagettftext($image, $this->properties['fontSize'] * 1.4, $ghostChars[$index]['angle'], $ghostChars[$index]['x'] + $centeringGhost, $ghostChars[$index]['y'], $ghostTextColor, $font, $ghostChars[$index]['char']);
			}

			// Front chars
			imagettftext($image, $this->properties['fontSize'], $char['angle'], $char['x'] + $centeringFront, $char['y'], $textColor, $font, $char['char']);

			// Strikethrough
			if(
				$this->properties['lines'] === TRUE
				OR
				$this->properties['questionType'] === 'strikethrough'
				OR
				$this->properties['questionType'] === 'notStrikethrough'
			)
			{
				if(in_array($index, $strikethroughChars))
				{
					$x = mt_rand($char['x'] + $centeringFront - $char['width'] / 5, $char['x'] + $centeringFront);

					$rbit = mt_rand(0, 1);
					$hTop = ($char['y'] - $char['height']) + floor($char['height'] / 2);
					$hBottom = $char['y'] - floor($char['height'] / 3);

					$y = mt_rand(
						($rbit === 0) ? $hTop : $hBottom + floor($char['height'] / 3),
						($rbit === 0) ? $hBottom : $char['y']
					);
					$y2 = mt_rand(
						($rbit === 0) ? $hBottom + floor($char['height'] / 3) : $hTop,
						($rbit === 0) ? $char['y'] : $hBottom
					);

					imagefilledpolygon($image, array(
				        $x, $y,
				        $x + $char['width'], $y2,
				        $x + $char['width'], $y2 - $this->properties['lineWidth'],
				        $x, $y - $this->properties['lineWidth']
				    ),
				    4,
				    $lineColor);
				}
			}

			// Code
			if($this->properties['questionType'] === 'strikethrough' AND in_array($index, $strikethroughChars))
			{
				$code .= $char['char'];
			}
			elseif($this->properties['questionType'] === 'notStrikethrough' AND !in_array($index, $strikethroughChars))
			{
				$code .= $char['char'];
			}
			elseif($this->properties['questionType'] === 'ghost')
			{
				$code .= $ghostChars[$index]['char'];
			}
			elseif($this->properties['questionType'] === 'normal')
			{
				$code .= $char['char'];
			}
		}

		if($this->properties['ltr'] === FALSE)
		{
			$code = strrev($code);
		}

		$_SESSION[$this::$sessionNamespace][$this->properties['name']] = serialize(array(
			'code' => $code,
			'timestamp' => time(),
			'minTimeLapse' => $this->properties['minTimeLapse'],
			'maxTimeLapse' => $this->properties['maxTimeLapse']
		));

		return $image;
	}

	/**
	 * Output image.
	 */
	public function render()
	{
		$image = $this->createImage();

		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Pragma: no-cache");

		switch($this->properties['imageType'])
		{
			case 'jpeg':
				header('Content-type: image/jpeg');
				imagejpeg($image);
				break;
			case 'gif':
				header('Content-type: image/gif');
				imagegif($image);
				break;
			case 'png':
			default:
				header('Content-type: image/png');
				imagepng($image);
				break;
		}

		imagedestroy($image);
	}

	/**
	 * @return string
	 */
	public function base64()
	{
		$image = $this->createImage();

		$data_uri = 'data:image/png;base64,';

		ob_start(); 

		switch($this->properties['imageType'])
		{
			case 'jpeg':
				$data_uri = 'data:image/jpeg;base64,';
				imagejpeg($image);
				break;
			case 'gif':
				$data_uri = 'data:image/gif;base64,';
				imagegif($image);
				break;
			case 'png':
			default:
				imagepng($image);
				break;
		}

		$imageData = ob_get_contents();

		ob_end_clean(); 

		imagedestroy($image);

		return $data_uri.base64_encode($imageData);
	}

	/**
	 * @param string $userInput
	 * @param string $captchaName
	 * @return bool
	 */
	public static function valid($userInput, $captchaName = NULL)
	{
		$valid = FALSE;

		if( !$captchaName) $captchaName = 'default';

		if(
			! empty($userInput)
			AND
			array_key_exists(self::$sessionNamespace, $_SESSION)
			AND
			array_key_exists($captchaName, $_SESSION[self::$sessionNamespace])
		)
		{
			$captcha = unserialize($_SESSION[self::$sessionNamespace][$captchaName]);

			$_SESSION[self::$sessionNamespace][$captchaName] = NULL;

			$valid = (
				time() - $captcha['timestamp'] > $captcha['minTimeLapse']
				AND
				time() - $captcha['timestamp'] < $captcha['maxTimeLapse']
				AND
				$userInput === $captcha['code']
			);
		}

		unset($_SESSION[self::$sessionNamespace][$captchaName]);

		return $valid;
	}

	/**
	 * @return string String length determined by "length" property.
	 */
	protected function getString()
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$string = '';
		for($i = 0; $i < $this->properties['length']; $i++)
		{
			$string .= $chars[mt_rand(0, strlen($chars)-1)];
		}
		return $string;
	}

	/**
	 * @param string|array $color Hexadecimal value, string of comma separated RGB values or indexed array with RGB values.
	 * @param array $default Indexed array with default RGB values to return if $color could not be parsed.
	 * @return array Indexed array with RGB values.
	 */
	protected function parseColor($color, array $default = array(255, 255, 255))
	{
		if(is_string($color) AND strpos($color, '#') === 0 AND in_array(strlen($color), array(4, 7)))
		{
			// Hex
			$color = ltrim($color, '#');
			if(strlen($color) == 3) {
				$color = array(
					hexdec(substr($color, 0, 1).substr($color, 0, 1)),
					hexdec(substr($color, 1, 1).substr($color, 1, 1)),
					hexdec(substr($color, 2, 1).substr($color, 2, 1))
				);
			} else {
				$color = array(
					hexdec(substr($color, 0, 2)),
					hexdec(substr($color, 2, 2)),
					hexdec(substr($color, 4, 2))
				);
			}
		}
		elseif( (is_string($color) AND substr_count($color, ',') === 2) OR (is_array($color) AND count($color) === 3) )
		{
			// RGB
			if( !is_array($color)) $color = array_map('intval', explode(',', $color));
		}
		else
		{
			$color = $default;
		}

		return $color;
	}
}