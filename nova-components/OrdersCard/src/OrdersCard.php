<?php

namespace Muradalwan\OrdersCard;

use Laravel\Nova\Card;


class OrdersCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'orders-card';
    }

    public function authUser()
    {
        return $this->withMeta(['authUser' => auth()->user()]);
    }
}
