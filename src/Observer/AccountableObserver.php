<?php

namespace TestMonitor\Accountable\Observer;

use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\AccountableServiceProvider;

class AccountableObserver
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * AccountableObserver constructor.
     */
    public function __construct()
    {
        $this->config = config('accountable');
    }

    /**
     * @return mixed
     */
    protected function accountableUserId()
    {
        $user = AccountableServiceProvider::accountableUser();

        return ! is_null($user) ? $user->getKey() : null;
    }

    /**
     * Store the user creating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating($model)
    {
        if ($model->accountableEnabled()) {
            $model->{$this->config['column_names']['created_by']} = $this->accountableUserId();
            $model->{$this->config['column_names']['updated_by']} = $this->accountableUserId();
        }
    }

    /**
     * Store the user updating a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating($model)
    {
        if ($model->accountableEnabled()) {
            $model->{$this->config['column_names']['updated_by']} = $this->accountableUserId();
        }
    }

    /**
     * Store the user deleting a record.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleting($model)
    {
        if ($model->accountableEnabled() &&
            collect(class_uses($model))->contains(SoftDeletes::class)) {
            $model->{$this->config['column_names']['deleted_by']} = $this->accountableUserId();

            $model->save();
        }
    }
}
