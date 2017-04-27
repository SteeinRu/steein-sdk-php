<?php
/**
 * Copyright (c) 2017 Steein, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Steein.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Steein\SDK\Support;

use Steein\SDK\Exceptions\SteeinSDKException;
use Steein\SDK\Support\Models\PostModel;
use Steein\SDK\Support\Models\UserDefinitionModel;
use Steein\SDK\Support\Models\UserModel;

/**
 * Class ModelFactory
 *
 * @package Steein\SDK
*/
class ModelFactory extends SignatureParameters
{
    /**
     * Попытка преобразовать объект SteeinResponse в Model.
     *
     * @param string|null $subclassName Подкласс класса Model для преобразования в.
     *
     * @return Model
     *
     * @throws SteeinSDKException
     */
    public function makeApiObject($subclassName = null)
    {
        $this->validateResponseAsArray();
        //$this->validateResponseCastableAsModel();

        return $this->castAsModelOrModelEdge($this->decodedBody, $subclassName);
    }

    /**
     * Удобный способ для создания коллекции ApiUser.
     *
     * @return Model|UserModel
     *
     * @throws SteeinSDKException
     */
    public function makeUserModel()
    {
        return $this->makeApiObject(UserModel::class);
    }

    /**
     * Удобный способ для создания коллекции ApiPost.
     *
     * @return Model|PostModel
     *
     * @throws SteeinSDKException
     */
    public function makePostModel()
    {
        return $this->makeApiObject(PostModel::class);
    }
}