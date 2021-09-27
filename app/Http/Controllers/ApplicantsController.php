<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicants;
use App\Models\JobsSearch;
use App\Models\MyJobs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Rules\MatchOldPassword;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ApplicantsController extends Controller
{
    public function applicants_login()
    {
        return view('auth.applicants_login');
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    public function applicants_register()
    {
        return view('auth.applicants_register');
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    public function applicants_save(Request $request)
    {
        // register
        // validate requests
        $request->validate([
            'app_name' => 'required',
            'app_email' => 'required|email|unique:applicants',
            'app_password' => 'required|min:5|max:12',
        ]);

        // insert data into database
        $app = new Applicants;
        $app->app_name = $request->app_name;
        $app->app_email = $request->app_email;
        $app->app_password = Hash::make($request->app_password);
        $save = $app->save();

        if ($save) {
            return back()->with('success', 'เพิ่มบัญชีใหม่เรียบร้อยแล้ว');
        } else {
            return back()->with('fail', 'เกิดข้อผิดพลาด ลองอีกครั้ง');
        }
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    public function applicants_check(Request $request)
    {
        // return $request->input();

        // login
        //validate request
        $request->validate([
            'app_email' => 'required|email',
            'app_password' => 'required|min:5|max:12'
        ]);

        $appInfo = Applicants::where('app_email', '=', $request->app_email)->first();

        if (!$appInfo) {
            return back()->with('fail', 'ไม่รู้จักบัญชีนี้');
        } else {
            // check password
            if (Hash::check($request->app_password, $appInfo->app_password)) {
                $request->session()->put('LoggedApp', $appInfo->app_name);
                return redirect('applicants/applicants_home');
            } else {
                return back()->with('fail', 'รหัสผ่านผิด');
            }
        }
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    // logout
    public function applicants_logout()
    {
        if (session()->has('LoggedApp')) {
            session()->pull('LoggedApp');
            return redirect('/applicants/applicants_home');
        }
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    // display index page
    public function applicants_index()
    {
        $data = ['LoggedAppInfo' => Applicants::where('app_id', '=', session('LoggedApp'))->first()];
        return view('applicants.applicants_index', $data);
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    // display my jobs page
    public function applicants_myjobs(Request $request)
    {
        if ($request->input('type') == 'all') {
            $all = MyJobs::all();
            return view('applicants.applicants_myjobs', compact('all'));
        } elseif ($request->input('type') == 'FavoriteJobs') {
            $favoite = MyJobs::query()->where('action_type', 'LIKE', 'FavoriteJobs')->get();
            return view('applicants.applicants_myjobs', compact('favoite'));
        } elseif ($request->input('type') == 'AppliForm') {
            $row = MyJobs::query()->where('action_type', 'LIKE', 'AppliForm')
                // ->orWhere('action_type', 'LIKE', 'ApproveForm')
                // ->orWhere('action_type', 'LIKE', 'RejectForm')
                ->get();

            $rove = MyJobs::query()->where('a_id', 'LIKE', '4')->get();
            $rej = MyJobs::query()->where('a_id', 'LIKE', '5')->get();
            return view('applicants.applicants_myjobs', compact('row', 'rove', 'rej'));
        } else {
            $row = MyJobs::query()->where('action_type', 'LIKE', $request->input('type'))->get();
            return view('applicants.applicants_myjobs');
        }
        // $favoite = MyJobs::query()->where('a_id', 'LIKE', '1')->get();
        // $rove = MyJobs::query()->where('a_id', 'LIKE', '4')->get();
        // $rej = MyJobs::query()->where('a_id', 'LIKE', '5')->get();
        // return view('applicants.applicants_myjobs', compact('all', 'favoite', 'rove', 'rej'));
    }

    public function applicants_delete_myjobs($myjobs_id)
    {
        $myjobs = MyJobs::find($myjobs_id);

        $myjobs->delete();

        return redirect()->route('myjobs')->with('success', 'ลบข้อมูลผู้สมัครเรียบร้อย');
    }

    // ============================================================================================
    // ============================================================================================ 

    public function ent_check_app()
    {
        // $wait = MyJobs::query()->where('a_id', 'LIKE', '2')->get();
        // $file_save = MyJobs::query()->where('a_id', 'LIKE', '3')->get();
        // $rove = MyJobs::query()->where('a_id', 'LIKE', '4')->get();

        // $mamber = DB::table('my_jobs')
        //     ->join('histories', 'history_id', "=", "my_jobs.history_id")
        //     ->get();

        // approv
        $mamber = MyJobs::join('histories', 'histories.history_id', "=", "my_jobs.history_id")
            ->where('a_id', 'LIKE', '2')
            ->get();

        // $wait = MyJobs::query()->where('a_id', 'LIKE', '2')->get();

        // save into file
        $file_save = MyJobs::join('histories', 'histories.history_id', "=", "my_jobs.history_id")
            ->where('a_id', 'LIKE', '3')
            ->get();
        // $file_save = MyJobs::query()->where('a_id', 'LIKE', '3')->get();

        // approv
        $rove = MyJobs::join('histories', 'histories.history_id', "=", "my_jobs.history_id")
            ->where('a_id', 'LIKE', '4')
            ->get();
        // $rove = MyJobs::query()->where('a_id', 'LIKE', '4')->get();
        return view('ent.ent_check_app', compact('mamber', 'file_save', 'rove'));
    }

    // ============================================================================================
    // ============================================================================================ 
    // approv
    public function ent_approve($myjobs_id)
    {
        $mamber = MyJobs::find($myjobs_id);
        return view('ent.ent_approve', ['mamber' => $mamber]);
    }

    // update approv
    public function ent_update_approve(Request $request)
    {
        // return $request->input();
        $data = MyJobs::find($request->myjobs_id);
        $data->action_type = $request->action_type;
        $data->a_id = $request->a_id;
        $data->save();
        return redirect('ent/ent_check_app');
    }

    // ============================================================================================
    // ============================================================================================ 
    // reject
    public function ent_reject($myjobs_id)
    {
        $data = MyJobs::find($myjobs_id);
        return view('ent.ent_reject', ['data' => $data]);
    }

    // uodate reject
    public function ent_update_reject(Request $request)
    {
        // return $request->input();
        $data = MyJobs::find($request->myjobs_id);
        $data->action_type = $request->action_type;
        $data->a_id = $request->a_id;
        $data->save();
        return redirect('ent/ent_check_app');
    }

    // ============================================================================================
    // ============================================================================================ 
    // save file
    public function ent_save_file($myjobs_id)
    {
        $data = MyJobs::find($myjobs_id);
        return view('ent.ent_save_file', ['data' => $data]);
    }

    // update save file
    public function ent_update_save_file(Request $request)
    {
        // return $request->input();
        $data = MyJobs::find($request->myjobs_id);
        $data->a_id = $request->a_id;
        $data->save();
        return redirect('ent/ent_check_app');
    }
    // ============================================================================================
    // ============================================================================================ 

    public function ent_delete_apply($myjobs_id)
    {
        $datas = MyJobs::find($myjobs_id);

        $datas->delete();

        return redirect()->route('ent_check_app')->with('success', 'ลบข้อมูลผู้สมัครเรียบร้อย');
    }

    // ============================================================================================
    // ============================================================================================ 

    public function see_detail_jobs($jobs_id)
    {
        $all = MyJobs::find($jobs_id);
        return view('applicants.applicants_see_detail_jobs', compact('all'));
    }

    // ============================================================================================
    // ============================================================================================ 

    public function see_detail_search($jobs_id)
    {
        $jobs = JobsSearch::find($jobs_id);
        return view('applicants.applicants_see_detail_search', compact('jobs'));
    }

    // =====================================================================================================================================
    // =====================================================================================================================================

    // profile page
    public function applicants_profile()
    {
        $app_profile = Applicants::all();
        return view('applicants.applicants_profile', compact('app_profile'));
    }

    // ====================================================================================================================================
    // ====================================================================================================================================

    public function edit_profile($app_id)
    {
        $password_edit = Applicants::find($app_id);
        return view('applicants.applicants_edit_profile', compact('password_edit'));
    }

    // ====================================================================================================================================
    // ==================================================================================================================================== 

    public function change_password(Request $request)
    {
        // dd('ok');

        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        // $hashedPassword = Auth::user()->app_password;
        // if (Hash::check($request->current_password, $hashedPassword)) {
        //     if (Hash::check($request->new_password, $hashedPassword)) {

        //         $password_edit = Applicants::find(Auth::user()->app_id);
        //         $password_edit->app_password = bcrypt($request->new_password);
        //         $password_edit->save();
        //         session()->flash('message', 'password updated successfully');
        //         return redirect()->back();
        //     } else {
        //         session()->flash('message', 'new password can not be the old password!');
        //         return redirect()->back();
        //     }
        // } else {
        //     session()->flash('message', 'old password doesnt matched');
        //     return redirect()->back();
        // }
        return redirect()->route('profile')->with('success', 'แก้ไขข้อมูลเข้าสู่ระบบเรียบร้อย');
    }

    // ====================================================================================================================================
    // ==================================================================================================================================== 

    // search from text search and option jobs post
    public function test_search(Request $request)
    {
        $hosts = ["http://127.0.0.1:9200"];

        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();

        // ============ get advance query index =============
        if ($request->get('query')) {
            $query = $request->get('query');
            $params = [
                'index' => 'jobs_searches',
                'body' => [

                    // ======================= analysis ==================
                    // 'setting' => [
                    //     'analysis' => [
                    //         'analyzer' => [
                    //             'autocomplete' => [
                    //                 'tokenlzer' => 'autocomplete',
                    //                 'filter' => [
                    //                     'lowercase',
                    //                 ]
                    //             ],
                    //             'autocomplete_search' => [
                    //                 'tokenlzer' => 'lowercase',
                    //             ]
                    //         ],
                    //         'tokenizer' => [
                    //             'autocomplete' => [
                    //                 'type' => 'edge_ngram',
                    //                 'min_gram' => 1,
                    //                 'max_gram' => 20,
                    //                 'token_chars' => [
                    //                     'letter',
                    //                     'whitespace'
                    //                 ]
                    //             ]
                    //         ]
                    //     ],
                    // ],
                    // 'mappings' => [
                    //     'properties' => [
                    //         'jobs_name' => [
                    //             'type' => 'text',
                    //             'analyzer' => 'autocomplete',
                    //             'search_analyzer' => 'autocomplete_search'
                    //         ],
                    //     ]
                    // ],

                    // 'query' => [
                    //     'match' => [
                    //         'jobs_name' => [
                    //             'query' => $query,
                    //             'operator' => 'and'
                    //         ]
                    //     ]
                    // ]

                    // =============================================================
                    // fuzzy
                    'query' => [
                        'multi_match' => [
                            'fields' => [
                                'jobs_name_company',
                                'jobs_name',
                                'jobs_type',
                                'jobs_detail',
                                'jobs_address',
                            ],
                            'query' => "*" . $query . "*",
                            'fuzziness' => 'AUTO'
                        ]
                    ]

                ]
            ];
            $ent_post0 = $client->search($params);
        } else {
            $query = "";
            $params = [
                'index' => 'jobs_searches_1631520417',
                'body' => [
                    'query' => [
                        'wildcard' => [
                            'jobs_name_company' => "*"
                        ]
                    ]
                ]
            ];

            $ent_post0 = $client->search($params);
        }

        // selected options
        $options = array();
        $options = [
            'jobs_name' => [],
            'jobs_type' => [],
            'jobs_name_company' => [],
            'start_post' => []
        ];

        foreach ($ent_post0["hits"]["hits"] as $v) {
            foreach ($options as $key => $b) {
                if (!in_array($v['_source'][$key], $options[$key])) {
                    array_push($options[$key], $v['_source'][$key]);
                }
            }
        }

        $ent_post = array_filter($ent_post0["hits"]["hits"], function ($v)  use ($request) {
            foreach ($request->all() as $query_all => $val) {
                if ($query_all != "query" && trim($v["_source"][$query_all]) != trim($val)) {
                    return false;
                }
            }
            return true;
        });

        // dd($options);

        return view('applicants.applicants_search', compact('ent_post', 'options', 'query'));
    }
}
