<?php require __DIR__.'/lib.php'; start_secure_session(); $csrf=csrf_token(); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ELink API Console</title><link rel="stylesheet" href="style.css"></head><body>
<main class="wrap"><header><div><h1>ELink API Console</h1><p>Select a work area, dataset, and batch, then run and monitor the AXIS task.</p></div><button id="logout" class="secondary hidden">Log out</button></header>
<section id="loginCard" class="card"><h2>Sign in</h2><form id="loginForm"><label>Username<input id="username" autocomplete="username" required></label><label>Password<input id="password" type="password" autocomplete="current-password" required></label><button>Login</button></form></section>
<section id="app" class="hidden">
<section class="card"><h2>1. Choose a batch</h2><div class="selector-grid">
<div><label>Work area filter</label><div class="inline"><input id="workAreaSearch" placeholder="Optional"><button id="loadWorkAreas" type="button">Load</button></div><label>Work area</label><select id="workArea" disabled><option value="">Load work areas first</option></select><small id="workAreaInfo"></small></div>
<div><label>Dataset filter</label><div class="inline"><input id="datasetSearch" placeholder="Optional"><button id="loadDatasets" type="button" disabled>Load</button></div><label>Dataset</label><select id="dataset" disabled><option value="">Select a work area first</option></select><small id="datasetInfo"></small></div>
<div><label>Batch filter</label><div class="inline"><input id="batchSearch" placeholder="Optional"><button id="loadBatches" type="button" disabled>Load</button></div><label>Batch</label><select id="batch" disabled><option value="">Select a dataset first</option></select><small id="batchInfo"></small></div>
</div></section>
<div class="grid"><section class="card"><h2>2. Configure and run</h2><form id="runForm"><label>GridLink queue<input id="gridlink_queue" placeholder="Optional"></label><label>Parameters JSON<textarea id="params" rows="7">{}</textarea></label><button id="submitBatch" disabled>Submit selected batch</button></form></section>
<section class="card"><h2>3. Task status</h2><label>Task ID<input id="taskId" placeholder="Returned after submission"></label><div class="actions"><button id="check" type="button">Check status</button><button id="results" type="button" class="secondary">Get results</button></div><p id="polling" class="muted"></p></section></div>
<section class="card"><h2>API response</h2><pre id="output">Ready.</pre></section></section></main>
<script>window.APP_CSRF=<?=json_encode($csrf)?>;</script><script src="app.js"></script></body></html>
