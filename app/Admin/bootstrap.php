<?php

/**
 *Laravel-admin - 基于 Laravel 的管理员构建器。
  @author z-song <https:github.comz-song>

  管理员的引导程序。

  在这里您可以删除内置表单字段：
  Encore\Admin\Form::forget(['map', 'editor']);

  或扩展自定义表单字段：
  Encore\Admin\Form::extend('php', PHPEditor::class);

  或者需要 js 和 css 资源：
  Admin::css('packagesprettydocscssstyles.css');
  Admin::js('packagesprettydocsjsmain.js');
 *
 */
Encore\Admin\Form::forget(['map', 'editor']);
app('view')->prependNamespace('admin', resource_path('views/admin'));
Encore\Admin\Form::init(function (Encore\Admin\Form $form) {

    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();
    $form->tools(function (Encore\Admin\Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
        // $tools->disableList();
    });
});

Encore\Admin\Grid::init(function (Encore\Admin\Grid $grid) {

    // $grid->disableActions();

    // $grid->disablePagination();

    // $grid->disableCreateButton();

    // $grid->disableFilter();

    // $grid->disableRowSelector();

    // $grid->disableColumnSelector();

    // $grid->disableTools();

    $grid->disableExport();
});
