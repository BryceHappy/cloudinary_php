<?php
/**
 * This file is part of the Cloudinary PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\Test\Transformation\Image;

use Cloudinary\Transformation\Background;
use Cloudinary\Transformation\CompassGravity;
use Cloudinary\Transformation\CompassPosition;
use Cloudinary\Transformation\Crop;
use Cloudinary\Transformation\Fill;
use Cloudinary\Transformation\FillPad;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Pad;
use Cloudinary\Transformation\Parameter;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Scale;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class SampleTest
 */
final class ResizeTest extends TestCase
{
    public function testScale()
    {
        $scale = Scale::scale();

        $this->assertEquals(
            'c_scale',
            (string)$scale
        );
//        $this->assertEquals(
//            '{"name":"resize","parameters":{"crop":"scale"}}',
//            json_encode($scale)
//        );

        $scaleWithParams = Scale::scale(100, 200);

        $this->assertEquals(
            'c_scale,h_200,w_100',
            (string)$scaleWithParams
        );

//        $this->assertEquals(
//            '{"name":"resize","parameters":{"crop":"scale","dimensions":{"width":100,"height":200}}}',
//            json_encode($scaleWithParams)
//        );

        $scaleWithBuiltParams = Scale::scale()->width(100)->height(200)->aspectRatio(0.5);

        $this->assertEquals(
            'ar_0.5,c_scale,h_200,w_100',
            (string)$scaleWithBuiltParams
        );

        $scaleWithLiquidGravity = Scale::scale(100, 200)
                                       ->liquidRescaling()
                                       ->ignoreAspectRatio(true);

        $this->assertEquals(
            'c_scale,fl_ignore_aspect_ratio,g_liquid,h_200,w_100',
            (string)$scaleWithLiquidGravity
        );

//        $this->assertEquals(
//            '{"name":"resize","parameters":{"crop":"scale","dimensions":{"width":100,"height":200},'.
//            '"gravity":"liquid"},'.
//            '"flags":{"flag":"ignore_aspect_ratio"}}',
//            json_encode($scaleWithLiquidGravity)
//        );

        $limitFit = Scale::limitFit();
        $this->assertEquals(
            'c_limit',
            (string)$limitFit
        );

//        $this->assertEquals(
//            '{"name":"resize","parameters":{"crop":"limit"}}',
//            json_encode($limitFit)
//        );

        $fit = Scale::fit();
        $this->assertEquals(
            'c_fit',
            (string)$fit
        );

//        $this->assertEquals(
//            '{"name":"resize","parameters":{"crop":"fit"}}',
//            json_encode($fit)
//        );

        $scaleWithDPR = Scale::scale(100, 200)->dpr(2.5);

        $this->assertEquals(
            'c_scale,dpr_2.5,h_200,w_100',
            (string)$scaleWithDPR
        );

        $this->assertEquals(
            'c_mfit',
            (string)Scale::minimumFit()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Liquid Rescaling is not supported for
     */
    public function testLiquidForUnsupportedCropMode()
    {
        Scale::limitFit(100, 200)->liquidRescaling();
    }

    public function testPad()
    {
        $pad = Pad::pad();

        $this->assertEquals(
            'c_pad',
            (string)$pad
        );

        $this->assertEquals(
            '{"name":"resize","parameters":[{"crop_mode":"pad"}]}',
            json_encode($pad)
        );

        $padWithParams = Pad::minimumPad(100, 200);

        $this->assertEquals(
            'c_mpad,h_200,w_100',
            (string)$padWithParams
        );

//        $this->assertEquals(
//            '{"name":"resize","parameters":[{"crop":"mpad","dimensions":{"width":100,"height":200}}}]',
//            json_encode($padWithParams)
//        );

        $padWithBuiltParams = Pad::pad(100, 200)->offset(50, 100)->background(Background::red());

        $this->assertEquals(
            'b_red,c_pad,h_200,w_100,x_50,y_100',
            (string)$padWithBuiltParams
        );

        $padWithGravity = Pad::pad(100, 200)->gravity(CompassGravity::NORTH_WEST);

        $this->assertEquals(
            'c_pad,g_north_west,h_200,w_100',
            (string)$padWithGravity
        );

        $lpadWithPosition = Pad::limitPad(100, 200)->position(
            new CompassPosition(CompassGravity::NORTH_WEST, 50, 100)
        );

        $this->assertEquals(
            'c_lpad,g_north_west,h_200,w_100,x_50,y_100',
            (string)$lpadWithPosition
        );
    }

    public function testFillPad()
    {
        $fillPad = FillPad::fillPad(100, 200)->background(Background::red());

        $this->assertEquals(
            'b_red,c_fill_pad,g_auto,h_200,w_100',
            (string)$fillPad
        );
    }

    public function testFillPadNonAutoGravity()
    {
        $this->expectException(InvalidArgumentException::class);

        Resize::fillPad(100, 200, new CompassGravity());
    }

    public function testImagga()
    {
        $imaggaCrop = Resize::imaggaCrop(100, 200);

        $this->assertEquals(
            'c_imagga_crop,h_200,w_100',
            (string)$imaggaCrop
        );

        $imaggaScale = Resize::imaggaScale(100, 200);

        $this->assertEquals(
            'c_imagga_scale,h_200,w_100',
            (string)$imaggaScale
        );
    }

    public function testFill()
    {
        $fill = Fill::fill(100, 200, Gravity::auto());

        $this->assertEquals(
            'c_fill,g_auto,h_200,w_100',
            (string)$fill
        );

        $limitFill = Fill::limitFill(100, 200, Gravity::auto());

        $this->assertEquals(
            'c_lfill,g_auto,h_200,w_100',
            (string)$limitFill
        );
    }

    public function testCrop()
    {
        $crop = Crop::crop(100, 200)->x(10)->y(20);

        $this->assertEquals(
            'c_crop,h_200,w_100,x_10,y_20',
            (string)$crop
        );

        $thumb = Crop::thumbnail(100, 200, Gravity::auto())->zoom(0.5);

        $this->assertEquals(
            'c_thumb,g_auto,h_200,w_100,z_0.5',
            (string)$thumb
        );
    }

    public function testResize()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(
            'c_scale,fl_ignore_aspect_ratio,g_liquid,h_200,w_100',
            (string)Resize::scale(100, 200)->ignoreAspectRatio()->liquidRescaling()
        );

        $this->assertEquals(
            'c_crop,h_200,w_100,x_10,y_20',
            (string)Resize::crop(100, 200)->x(10)->y(20)
        );

        $this->assertEquals(
            'c_custom,cu_v1:17,h_200,w_100,x_10,y_20',
            (string)Resize::generic('custom', 100, 200)->addParameter(Parameter::generic('cu', 'v1', 17))
                          ->x(10)->y(20)
        );
    }
}
