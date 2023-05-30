<?php
namespace App\Grid\Displayers;



use App\Grid\Actions\Edit;
use App\Grid\Actions\Show;

use App\Grid\Actions\Delete;
use Encore\Admin\Grid\Displayers\DropdownActions;

class BtnActions extends DropdownActions
{
    protected $view = 'admin::grid.actions.button';


    protected $defaultClass = [Edit::class, Show::class, Delete::class];
    
    /**
     * Disable view action.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableView(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Show::class);
        } elseif (!in_array(Show::class, $this->defaultClass)) {
            array_push($this->defaultClass, Show::class);
        }

        return $this;
    }

    /**
     * Disable delete.
     *
     * @param bool $disable
     *
     * @return $this.
     */
    public function disableDelete(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Delete::class);
        } elseif (!in_array(Delete::class, $this->defaultClass)) {
            array_push($this->defaultClass, Delete::class);
        }

        return $this;
    }

    /**
     * Disable edit.
     *
     * @param bool $disable
     *
     * @return $this
     */
    public function disableEdit(bool $disable = true)
    {
        if ($disable) {
            array_delete($this->defaultClass, Edit::class);
        } elseif (!in_array(Edit::class, $this->defaultClass)) {
            array_push($this->defaultClass, Edit::class);
        }

        return $this;
    }
}