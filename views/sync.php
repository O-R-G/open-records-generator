<?php
    $request_url = '/open-records-generator/lib/sync-' . $syncName . '.php';
?>
<div id="body-container">
    <div id="body">
        <div>
            <form id="sync-form" action="/lib" method="post" enctype="multipart/form-data">
                <span>Sync<?php echo $syncName ? ' with ' . $syncName : ''; ?></span>
                <input name='action' type='hidden' value='sync'>
                <input name='submit' type='submit' value='Sync'>
            </form>
        </div>
    </div>
</div>
<script>
    let sync_form = document.getElementById('sync-form');
    let request_url = '<?php echo $request_url; ?>';
    let request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readyState == 4 && request.status == 200)
        {
            let response = JSON.parse(request.responseText);
            if(response['status'] == 'success')
            {
                let div = document.createElement('DIV');
                div.id = 'sync-result-container';
                div.innerHTML = response['body'];
                sync_form.parentElement.replaceChild(div, sync_form);
            }
        }
    }
    request.open('POST', request_url, true);
    sync_form.addEventListener('submit', function(event){
        console.log('submit');
        event.preventDefault();
        let data = new FormData(event.target);
        request.send(data);
    });
</script>
<?php

