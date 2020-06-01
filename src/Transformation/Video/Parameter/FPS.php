<?php
/**
 * This file is part of the Cloudinary PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\Transformation;

use Cloudinary\ArrayUtils;
use Cloudinary\Transformation\Parameter\BaseParameter;

/**
 * Class FPS
 *
 * @property MinMaxRange value
 */
class FPS extends BaseParameter
{
    const VALUE_CLASS = MinMaxRange::class;
    /**
     * FPS constructor.
     *
     * @param      $min
     * @param null $max
     */
    public function __construct($min = null, $max = null)
    {
        parent::__construct($min, $max);
    }

    /**
     * Sets the minimum frame rate.
     *
     * @param int $min The minimum frame rate in frames per second.
     *
     * @return $this
     */
    public function min($min)
    {
        $this->value->min($min);

        return $this;
    }

    /**
     * Sets the maximum frame rate.
     *
     * @param int $max The maximum frame rate in frames per second.
     *
     * @return $this
     */
    public function max($max)
    {
        $this->value->max($max);

        return $this;
    }

    /**
     * Creates a new instance using provided parameters array.
     *
     * @param string|array $params The parameters.
     *
     * @return FPS
     */
    public static function fromParams($params)
    {
        $params = ArrayUtils::build($params);

        return (new static(...$params));
    }
}
