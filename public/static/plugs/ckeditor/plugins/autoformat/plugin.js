(function() {
    CKEDITOR.plugins.add('autoformat', {
        //requires: ['styles', 'button'],

        init: function(a) {
          //  a.ui.addButton("autoformat", {label: "上传本地图片", command: 'uimage', icon: 'image', toolbar: 'insert,10'});
            a.addCommand('autoformat', CKEDITOR.plugins.autoformat.commands.autoformat);
            a.ui.addButton('autoformat', {
                label: "清除格式，一键排版",
                command: 'autoformat',
                //这个autoformat.gif是你的插件图标，放在同目录下
                icon: 'source',//this.path + "autoformat.gif"
            });
        }
    });
    CKEDITOR.plugins.autoformat = {
        commands: {
            autoformat: {
                exec: function(a) {
                    var _html = a.getData();
                    //清除样式代码
                    _html = _html.replace(/<div/ig, '<p');
                    _html = _html.replace(/<\/div>/ig, '</p>');
                    _html = _html.replace(/<strong[^>]*>/ig, '');
                    _html = _html.replace(/<\/strong>/ig, '');
                    _html = _html.replace(/<em[^>]*>/ig, '');
                    _html = _html.replace(/<\/em>/ig, '');
                    _html = _html.replace(/<u[^>]*>/ig, '');
                    _html = _html.replace(/<\/u>/, '');
                    _html = _html.replace(/<li[^>]*>/ig, '');
                    _html = _html.replace(/<\/li>/ig, '');
                    _html = _html.replace(/<span[^>]*>/ig, '');
                    _html = _html.replace(/<\/span>/ig, '');
                    _html = _html.replace(/&nbsp;/ig, '');
                    _html = _html.replace(/　/ig, '');
                    _html = _html.replace(/<p><\/p>/ig, '');
                    _html = _html.replace(/<a/ig, '<a rel="nofollow"');


                    //将p标签替换成<br />
                    _html = _html.replace(/<p[^>]*>/ig, '');
                    _html = _html.replace(/<\/p>/ig, '<br />');
                    _html = _html.replace(/<br \/><br \/>/ig, '<br />');
                    _html = _html.replace(/[\n]/ig, '');

                    //按<br />分组，将换行<br>全部替换成p标签
                    bb = _html.split("<br />");
                    aa = '';
                    for (var i = 0; i < bb.length; i++) {
                        aa = aa + '<p>' + bb[i] + '</p>';
                    }

                    //首行缩进
                    _html = aa.replace(/<p[^>]*>/ig, '<p>　　');
                    _html = _html.replace(/<p>　　<\/p>/ig, '');
                    _html = _html.replace(/<p><\/p>/ig, '');

                    //在这里执行你将_html中的空行替换掉的操作
                    a.setData(_html);
                }
            }
        }
    };
})();