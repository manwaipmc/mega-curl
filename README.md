MegaCurl
========

This class allows you work easy with PHP CURL.

To start work with this component just create new object.

~~~~~~ php
$MegaCurl = new MegaCurl();
~~~~~

Now in your controller (for example):

~~~~~~ php
$result = $MegaCurl->setRequestUrl('http://store.com/products/index.xml')
            ->setOptions(array('FOLLOWLOCATION' => true))
            ->setHttpMethod('get')
            ->execute();
        // ...
        // some logic
        // ...
~~~~~

Or do POST ( for example, if our cart on another domain =) ):

~~~~~~ php
$data = $MegaCurl->setRequestUrl('http://store.com/products/addToCart')
            ->setOptions(array(
                'FOLLOWLOCATION' => true
            ))
            ->executePost(array(
                'id' => 1,
                'qty' => 2
            ));
~~~~~

But if you want to get Cart, you will see that it's empty, because every query start new session. For comfortable work
use  oneSession() method. (for example):

Add to cart
~~~~~~ php
$data = $MegaCurl->setRequestUrl('http://store.com/products/addToCart')
            ->oneSession()
            ->setOptions(array(
                'FOLLOWLOCATION' => true
            ))
            ->executePost(array(
                'id' => 1,
                'qty' => 2
            ));
~~~~~

Get cart
~~~~~~ php
$data = $MegaCurl->setRequestUrl('http://store.com/cart')
            ->oneSession()
            ->setHttpMethod('get')
            ->setOptions(array('FOLLOWLOCATION' => true))
            ->execute();
        return $data;
~~~~~

But now comes new problem. We have only one session for all users =). To fix this, just put in oneSession() method
unique file name for each user. (for example):

~~~~~~ php
$cookie_file = TMP.'cookie_user'.$SessionHandler->read('User.id').'.txt'
...->oneSession($cookie_file)...
~~~~~

Now we have separate sessions for each users.

Look in /tmp folder. You will see there something like that:

cookie_user125.txt
cookie_user65.txt

Easy, isn't it? =)