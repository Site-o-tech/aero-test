window.onload = function(){
    var form = document.querySelector('#product_addform');
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const url = form.getAttribute('action');
        var data = new FormData(form);

        fetch(url,
        {
            method: "POST",
            body: data
        })
        .then(response => {
            if (response.status !== 200) {
                return Promise.reject();
            }
            return response.text();
        })
        .then(function (data) {
            if(data != 'success'){
                alert(data);
            } else {
                document.location.reload(true);
            }
        })
        .catch(() => console.log('ошибка'));
    });

    document.querySelector('[data-role="addinput"]').addEventListener('click', function(){
        var code = this.getAttribute('data-target');
        var input = document.querySelector('[name="PROPERTIES['+code+'][]"]');
        var type = input.getAttribute('type');

        var new_input = document.createElement('input');
        new_input.setAttribute('type', type);
        new_input.setAttribute('name', 'PROPERTIES['+code+'][]');

        this.parentNode.insertBefore(new_input, this);
    });
}