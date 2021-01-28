<?php

namespace Muradalwan\TaxiOrder;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TaxiOrder extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('taxi-order', __DIR__ . '/../dist/js/tool.js');
        Nova::style('taxi-order', __DIR__ . '/../dist/css/tool.css');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('taxi-order::navigation');
    }

    public function currentVisitors()
    {
        return $this->withMeta(['currentVisitors' => auth()->user()]);
    }
}
