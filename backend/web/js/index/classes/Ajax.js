'use strict';

export class Ajax {
    /*
     * Вспомогатеьная функция отправки AJAX
     * 
     * @param {type} url
     * @param {array} data
     * @param {function} onSuсcess
     * @returns {}
     * 
     */
    static send (url, data, onSuccess) {
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: "json",
            success: function (response) {
                onSuccess(response);
            },
            error: function (jqXHR, exception) {
                if (jqXHR.status === 0) {
                    alert('Not connect. Verify Network.');
                } else if (jqXHR.status === 404) {
                    alert('Requested page not found (404).');
                } else if (jqXHR.status === 500) {
                    alert('Internal Server Error (500).');
                } else if (exception === 'parsererror') {
                    alert('Requested JSON parse failed.');
                } else if (exception === 'timeout') {
                    alert('Time out error.');
                } else if (exception === 'abort') {
                    alert('Ajax request aborted.');
                } else {
                    alert('Uncaught Error. ' + jqXHR.responseText);
                }
            }
        });
    }
    
    /*
     * Вспомогатеьная функция отправки AJAX с кнопки
     * 
     * @param {array} data Обязательно доджен содержать data['url']
     * @param {function} onSuсcess
     * @returns {}
     * 
     */
    static sendFromButton(data, onSuccess) {
        Ajax.send(data['url'], data, onSuccess);
    }
}