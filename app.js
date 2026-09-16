const $=s=>document.querySelector(s); let csrf=window.APP_CSRF, timer=null;
async function call(action, method='GET', body=null){
 const opt={method,headers:{'X-CSRF-Token':csrf}}; if(body!==null){opt.headers['Content-Type']='application/json';opt.body=JSON.stringify(body)}
 const r=await fetch('api.php?action='+action,opt); let data; try{data=await r.json()}catch{data={error:'Non-JSON response'}}
 show(data); if(!r.ok) throw new Error(data.error||`HTTP ${r.status}`); return data;
}
function show(v){$('#output').textContent=JSON.stringify(v,null,2)}
function auth(on){$('#loginCard').classList.toggle('hidden',on);$('#app').classList.toggle('hidden',!on);$('#logout').classList.toggle('hidden',!on)}
call('session').then(x=>{csrf=x.csrf;auth(x.authenticated)}).catch(()=>auth(false));
$('#loginForm').addEventListener('submit',async e=>{e.preventDefault();try{await call('login','POST',{username:$('#username').value,pwd:$('#password').value});$('#password').value='';auth(true)}catch(e){}});
$('#logout').onclick=async()=>{clearInterval(timer);await call('logout','POST',{});auth(false)};
$('#runForm').addEventListener('submit',async e=>{e.preventDefault();let params;try{params=JSON.parse($('#params').value||'{}')}catch{show({error:'Parameters must be valid JSON'});return}
 try{const x=await call('submit','POST',{dataset_path:$('#dataset_path').value,dataset:$('#dataset').value,batch_name:$('#batch_name').value,gridlink_queue:$('#gridlink_queue').value,params});if(x.id){$('#taskId').value=x.id;startPolling(x.id)}}catch(e){}});
async function status(id){return call('task&id='+encodeURIComponent(id))}
function startPolling(id){clearInterval(timer);$('#polling').textContent='Polling every 3 seconds.';timer=setInterval(async()=>{try{const x=await status(id);const item=Array.isArray(x.data)?x.data[0]:x;const s=String(item?.status??'').toLowerCase();if(['done','failed','completed','cancelled','canceled'].includes(s)){clearInterval(timer);$('#polling').textContent='Polling stopped: '+s;if(['done','completed'].includes(s)) await call('results&id='+encodeURIComponent(id));}}catch{}},3000)}
$('#check').onclick=()=>status($('#taskId').value.trim());
$('#results').onclick=()=>call('results&id='+encodeURIComponent($('#taskId').value.trim()));
