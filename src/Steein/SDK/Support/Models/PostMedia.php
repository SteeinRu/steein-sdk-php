<?php
/**
 * Created by PhpStorm.
 * User: steei
 * Date: 10.06.2017
 * Time: 10:58
 */

namespace Steein\SDK\Support\Models;

use Steein\SDK\Support\Model;

/**
 * Class PostMedia
 *
 * @package Steein\SDK
 */
class PostMedia extends Model
{
    /**
     * Возвращает Путь к фотографии
     *
     * @return string|null
     */
    public function getImage()
    {
        return $this->get('image');
    }
}