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
    let div_syncing = document.createElement('DIV');
    div_syncing.innerText = 'Syncing . . .';
    request.onreadystatechange = function(){
        if(request.readyState == 4 && request.status == 200)
        {
            let response = JSON.parse(request.responseText);
            if(response['status'] == 'success')
            {
                let div_report = document.createElement('DIV');
                div_report.id = 'sync-result-container';
                div_report.innerHTML = response['body'];
                sync_form.parentElement.replaceChild(div_report, sync_form);
                div_syncing.remove();
            }
        }
    }
    request.open('POST', request_url, true);
    sync_form.addEventListener('submit', function(event){
        event.preventDefault();
        sync_form.parentElement.appendChild(div_syncing);
        let data = new FormData(event.target);
        request.send(data);
    });
</script>
<?php

