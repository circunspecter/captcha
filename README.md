# GrindNous\Captcha

PHP Captcha system (session based): configurable look and different validation modes.

Features

- Configurable look
- Different types of questions/answers
- Timer: minimum and maximum time for validation
- Multiple instances

Requirements

- PHP 5.3+
- GD 2.0.1+ with FreeType support

## Usage

**Instantiation:**

    // Default
    $captcha = new \GrindNous\Captcha\Captcha();

    // Settings
    $captcha = new \GrindNous\Captcha\Captcha(array(
        'property' => 'value'
    ));

**Accessing properties:**

    // Get property
    $captcha->prop('property');

    // Set property
    $captcha->prop('property', 'value');

**Show captcha:**

    // captcha-generator.php
    $captcha->render();
    // captcha-request-page.php
    <img src="captcha-generator.php" />

    // captcha-request-page.php
    $encodedImage = $captcha->base64();
    echo '<img src="'.$encodedImage.' />';

**Validation:**

    // verify-page.php
    if(\GrindNous\Captcha\Captcha::valid( $userPostedData ))
        // Valid
    else
        // Not valid

Specifying the captcha to validate:

    // verify-page.php
    if(\GrindNous\Captcha\Captcha::valid( $userPostedData, $captchaName ))

##Properties

**name**

Name of the session var that will contain the captcha information.

    Default : 'default'
    Options : string

**questionType**

Specifies how will generate the validation code.

    Default : normal
    Options : normal | ghost | strikethrough | notStrikethrough

**ltr**

Left to right: indicates whether the introduction of characters have to be performed from left to right or vice versa.

    Default : TRUE
    Options : bool

**minTimeLapse**

Seconds that must elapse, at least, to take into account the data sent by the user.

    Default : 10
    Options : int

**maxTimeLapse**

Elapsed seconds to mark the captcha as invalid.

    Default : 600
    Options : int

**width**

Image width.

    Default : 140
    Options : int

**height**

Image height.

    Default : 60
    Options : int

**imageType**

Image format.

    Default : 'png'
    Options : 'jpeg' | 'gif' | 'png'

**bgColor**

Background color.

    Default : '#fff'
    Options : string | array

**textColor**

Text color.

    Default : '#333'
    Options : string | array


**ghostText**

Display a second set of semitransparent chars.

    Default : TRUE
    Options : bool

**ghostTextColo **

Color of semitransparent chars.

    Default : '#333'
    Options : string | array

**lines**

Strikethrough characters randomly.

    Default : TRUE
    Options : bool

**lineColor**

Color of lines.

    Default : '#333'
    Options : string | array

**lineWidth**

Width of the lines.

    Default : 2
    Options : int

**circles**

Add semitransparent circles.

    Default : TRUE
    Options : bool

**circleColor**

Color of circles.

    Default : '#333'
    Options : string | array

**length**

Number of characters.

    Default : 4
    Options : int

**fontFile**

TrueType font to use.

    Default : 'ttf_molten/molten.ttf'
    Options : string

**fontSize**

Font size.

    Default : 22
    Options : int

**letterSpacing**

Character spacing.

    Default : 0
    Options : int

__

1. Each type of question will affect the valid response; must inform the user to enter the correct answer on each case:

    - normal: insert all opaque chars (affected by textColor).
    - ghost: insert all semitransparent chars (affected by ghostTextColor).
    - strikethrough: insert only the strikethrough chars.
    - notStrikethrough: insert only the chars without strikethrough.

2. Color properties: hexadecimal color value, string of comma separated RGB values or indexed array with RGB values.

3. The font will be searched in the "fonts" folder starting from the location of the class file.