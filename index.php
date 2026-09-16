<?php require __DIR__.'/lib.php'; start_secure_session(); $csrf=csrf_token(); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ELink API Console</title><link rel="stylesheet" href="style.css"></head><body>
<main class="wrap"><header><div><h1>ELink API Console</h1><p>Log in, submit an AXIS batch, and monitor the asynchronous task.</p></div><button id="logout" class="secondary hidden">Log out</button></header>
<section id="loginCard" class="card"><h2>Sign in</h2><form id="loginForm"><label>Username<input id="username" autocomplete="username" required></label><label>Password<input id="password" type="password" autocomplete="current-password" required></label><button>Login</button></form></section>
<section id="app" class="hidden">
<div class="grid"><section class="card"><h2>Run AXIS batch</h2><form id="runForm">
<label>Dataset path<input id="dataset_path" placeholder="dev\\folder1" required></label>
<label>Dataset<input id="dataset" placeholder="sample-dataset" required></label>
<label>Batch name<input id="batch_name" placeholder="batch-name" required></label>
<label>GridLink queue<input id="gridlink_queue" placeholder="Optional"></label>
<label>Parameters JSON<textarea id="params" rows="7" placeholder='{"globalParam1":"value"}'>{}</textarea></label>
<button>Submit batch</button></form></section>
<section class="card"><h2>Task status</h2><label>Task ID<input id="taskId" placeholder="Returned after submission"></label><div class="actions"><button id="check" type="button">Check status</button><button id="results" type="button" class="secondary">Get results</button></div><p id="polling" class="muted"></p></section></div>
<section class="card"><h2>API response</h2><pre id="output">Ready.</pre></section></section></main>
<script>window.APP_CSRF=<?=json_encode($csrf)?>;</script><script src="app.js"></script></body></html>
