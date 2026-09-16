<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_secure_session();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$action = $_GET['action'] ?? '';
try {
    if ($action === 'session' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        json_response(['authenticated'=>!empty($_SESSION['elink_token']), 'csrf'=>csrf_token()]);
    }
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        $username=trim((string)($b['username']??'')); $pwd=(string)($b['pwd']??'');
        if ($username==='' || $pwd==='') json_response(['error'=>'Username and password are required'], 422);
        $r=api_request('POST','/srv-api/v0/account/login',['username'=>$username,'pwd'=>$pwd],[],false);
        if ($r['status']>=200 && $r['status']<300 && !empty($r['data']['token'])) {
            session_regenerate_id(true); $_SESSION['elink_token']=$r['data']['token'];
            json_response(['success'=>true]);
        }
        json_response($r['data'], $r['status'] ?: 502);
    }
    if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); unset($_SESSION['elink_token']); session_regenerate_id(true); json_response(['success'=>true]);
    }
    if ($action === 'work-areas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        $r=api_request('POST','/api/v0/work_areas',null,['search_pattern'=>(string)($b['search_pattern']??'')]);
        json_response($r['data'],$r['status']);
    }
    if ($action === 'datasets' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        $q=['logical_path'=>(string)($b['logical_path']??''),'search_pattern'=>(string)($b['search_pattern']??''),
            'max_depth'=>(int)($b['max_depth']??2),'retrieve_axis_version'=>(bool)($b['retrieve_axis_version']??true)];
        $r=api_request('POST','/api/v0/datasets',null,$q); json_response($r['data'],$r['status']);
    }
    if ($action === 'batches' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        foreach(['dataset_path','dataset'] as $k) if (trim((string)($b[$k]??''))==='') json_response(['error'=>"$k is required"],422);
        $q=['dataset_path'=>$b['dataset_path'],'dataset'=>$b['dataset'],'search_pattern'=>(string)($b['search_pattern']??'')];
        $r=api_request('POST','/api/v0/datasets/batches',null,$q); json_response($r['data'],$r['status']);
    }
    if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        foreach(['dataset_path','dataset','batch_name'] as $k) if (trim((string)($b[$k]??''))==='') json_response(['error'=>"$k is required"],422);
        $params=$b['params']??null; if ($params!==null && !is_array($params)) json_response(['error'=>'params must be a JSON object'],422);
        $payload=['dataset_path'=>$b['dataset_path'],'dataset'=>$b['dataset'],'batch_name'=>$b['batch_name'],
                  'gridlink_queue'=>($b['gridlink_queue']??'') ?: null,'params'=>$params];
        $r=api_request('POST','/api/v0/tasks/batches',$payload); json_response($r['data'],$r['status']);
    }
    if ($action === 'submit-glaas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf(); $b=read_json_body();
        foreach(['dataset_path','dataset','batch_name'] as $k) if (trim((string)($b[$k]??''))==='') json_response(['error'=>"$k is required"],422);
        $payload=['dataset_path'=>$b['dataset_path'],'dataset'=>$b['dataset'],'batch_name'=>$b['batch_name'],
          'glaas_farm'=>($b['glaas_farm']??'')?:null,'cores_to_use'=>isset($b['cores_to_use'])?(int)$b['cores_to_use']:null,
          'queues'=>($b['queues']??'')?:null,'max_runtime_in_minutes'=>isset($b['max_runtime_in_minutes'])?(int)$b['max_runtime_in_minutes']:null,
          'valuation_date'=>($b['valuation_date']??'')?:null,'params'=>$b['params']??null];
        $r=api_request('POST','/api/v0/glaas/jobs/submit',$payload); json_response($r['data'],$r['status']);
    }
    if ($action === 'task' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id=(string)($_GET['id']??''); if (!preg_match('/^[0-9a-fA-F-]{36}$/',$id)) json_response(['error'=>'Invalid task ID'],422);
        $r=api_request('GET','/api/v0/tasks',null,['id'=>$id]); json_response($r['data'],$r['status']);
    }
    if ($action === 'results' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id=(string)($_GET['id']??''); if (!preg_match('/^[0-9a-fA-F-]{36}$/',$id)) json_response(['error'=>'Invalid task ID'],422);
        $r=api_request('GET','/api/v0/tasks/'.$id.'/results'); json_response($r['data'],$r['status']);
    }
    json_response(['error'=>'Unknown route'],404);
} catch (Throwable $e) { json_response(['error'=>'Application error','detail'=>$e->getMessage()],500); }
