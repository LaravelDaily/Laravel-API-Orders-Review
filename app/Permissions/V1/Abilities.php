<?php

namespace App\Permissions\V1;

final class Abilities
{
    // Order Abilities - all orders
    public const View_All_Orders = 'order:view';

    public const Create_All_Orders = 'order:create';

    public const Update_All_Orders = 'order:update';

    public const Delete_All_Orders = 'order:delete';

    // Order Abilities - own order
    public const View_Own_Order = 'order:own:view';

    public const Create_Own_Order = 'order:own:create';

    public const Update_Own_Order = 'order:own:update';

    public const Delete_Own_Order = 'order:own:delete';

    // Product Abilities - all products
    public const Product_View = 'product:view';

    public static function getAbilities($user)
    {
        $abilities = [
            self::View_Own_Order,
            self::Create_Own_Order,
            self::Update_Own_Order,
            self::Delete_Own_Order,
            self::Product_View,
        ];

        return $abilities;

        if ($user->isAdmin()) {
            $abilities = [
                self::View_All_Orders,
                self::Create_All_Orders,
                self::Update_All_Orders,
                self::Delete_All_Orders,
                self::View_Own_Order,
                self::Create_Own_Order,
                self::Update_Own_Order,
                self::Delete_Own_Order,
                self::Product_View,
            ];
        }

        return $abilities;
    }
}
