<?php
namespace App\Admin\Actions\Pft;

use Encore\Admin\Admin;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Interactor\Dialog;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

/**
 * 删除门票
 */
class DeleteTicketAction extends RowAction
{
    public $name = '删 除';
    protected $selector = '.delete-ticket';
    public function handle(Model $model,Request $request)
    {    
        \App\UUModels\UUTicketDelete::create(['UUaid'=>$model->UUaid,'UUlid'=>$model->UUlid,'UUid'=>$model->UUid]);
        $model->delete();
        return $this->response()->success('门票已删除')->refresh();
    }
    
    // public function dialog(){
    //     $this->confirm('确定要删除这张门票吗？');
    // }
    public function render()
    {

        if ($href = $this->href()) {
            return "<a href='{$href}' class='btn btn-twitter btn-xs {$this->getElementClass()}'>{$this->name()}</a>";
        }
                
        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' onclick='deleteTicket()' class='btn btn-default btn-xs  %s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->getName())) : $this->name()
        );
        
    }

    public function addScript()
    {
        $parameters = json_encode($this->parameters());
        
        $calledClass = $this->getCalledClass();
        $route = $this->getHandleRoute();
        $script = <<<SCRIPT
function deleteTicket(){
    var target = event.target;
    var data = $(target).data();
    Object.assign(data, {$parameters});

    {$this->actionScript()}

    var actionResolver = function (data) {

        var response = data[0];
        var target   = data[1];

        if (typeof response !== 'object') {
            return $.admin.swal({type: 'error', title: 'Oops!'});
        }

        var then = function (then) {
            if (then.action == 'refresh') {
                $.admin.reload();
            }

            if (then.action == 'download') {
                window.open(then.value, '_blank');
            }

            if (then.action == 'redirect') {
                $.admin.redirect(then.value);
            }

            if (then.action == 'location') {
                window.location = then.value;
            }

            if (then.action == 'open') {
                window.open(then.value, '_blank');
            }
        };

        if (typeof response.html === 'string') {
            target.html(response.html);
        }

        if (typeof response.swal === 'object') {
            $.admin.swal(response.swal);
        }

        if (typeof response.toastr === 'object' && response.toastr.type) {
            $.admin.toastr[response.toastr.type](response.toastr.content, '', response.toastr.options);
        }

        if (response.then) {
          then(response.then);
        }
    };

    var actionCatcher = function (request) {
        if (request && typeof request.responseJSON === 'object') {
            $.admin.toastr.error(request.responseJSON.message, '', {positionClass:"toast-bottom-center", timeOut: 10000}).css("width","500px")
        }
    };
    

    var process = $.admin.swal({
        "type": "question",
        "showCancelButton": true,
        "showLoaderOnConfirm": true,
        "confirmButtonText": "\u63d0\u4ea4",
        "cancelButtonText": "\u53d6\u6d88",
        "title": "确定要删除这张门票吗？",
        "text": "",
        preConfirm: function(input) {
            return new Promise(function(resolve, reject) {
                Object.assign(data, {
                    _token: $.admin.token,
                    _action: '$calledClass',
                    _input: input,
                });

                $.ajax({
                    method: '{$this->getMethod()}',
                    url: '$route',
                    data: data,
                    success: function (data) {
                        resolve(data);
                    },
                    error:function(request){
                        reject(request);
                    }
                });
            });
        }
    }).then(function(result) {
        if (typeof result.dismiss !== 'undefined') {
            return Promise.reject();
        }
        
        if (typeof result.status === "boolean") {
            var response = result;
        } else {
            var response = result.value;
        }

        return [response, target];
    });

    {$this->handleActionPromise()}
}

SCRIPT;
    return $script;
    }
    
}