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
namespace Steein\SDK\Support\Models;

use Steein\SDK\Support\Model;

/**
 * Class UserModel
 *
 * @package Steein\SDK
 */
class UserModel extends Model
{
   /***
    * Возвращает идентификатор пользователя
    *
    * @return integer
   */
   public function getId()
   {
       return $this->get('id');
   }

   /***
    * Возвращает адрес электронной почты
    *
    * @return string|null
   */
   public function getEmail()
   {
       return $this->get('email');
   }

   /***
    * Возвращает индивидуальное имя пользователя
    *
    * @return string|null
   */
   public function getUsername()
   {
       return $this->get('username');
   }

   /***
    * Возвращает полное имя и фамилию
    *
    * @return string|null
   */
   public function getDisplayName()
   {
       return $this->get('displayName');
   }

    /***
     * Возращает Имя
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->get('name')['first_name'];
    }

    /***
     * Возращает Фамилию
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->get('name')['last_name'];
    }

    /***
     * Возращает информацию "О себе"
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->get('description');
    }

    /***
     * Возвращает Страну и Город
     *
     * @return string|null
    */
    public function getCountry()
    {
        return $this->get('country');
    }

    /***
     * Возращает ссылку на учетную записи в Steein
     *
     * @return string|null
    */
    public function getLink()
    {
        return $this->get('link');
    }

    /***
     * Возвращает статус "Подтврежденной страницы"
     *
     * @return integer
    */
    public function getVerified()
    {
        return $this->get('verified');
    }

    /***
     * Возвращает аватарку
     *
     * @return string|null
    */
    public function getAvatar()
    {
        return $this->get('avatar');
    }

    /***
     * Возвращает количество подписчиков
     *
     * @return integer
     */
    public function getCountFollowers()
    {
        return $this->get('action')['followers'];
    }

    /***
     * Возвращает количество пидписок
     *
     * @return integer
     */
    public function getCountFollowing()
    {
        return $this->get('action')['following'];
    }

    /***
     * Возвращает количество записей
     *
     * @return integer
     */
    public function getCountPosts()
    {
        return $this->get('action')['posts'];
    }
}