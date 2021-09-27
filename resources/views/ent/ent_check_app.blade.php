@extends('ent.layout')
@section('title','ตรวจสอบผู้สมัคร')

@section('content')

<body>

    <!-- ตรวจสอบผู้สมัคร -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="card" style="width: 100%; margin-top:100px;">
                <div class="card-header" style="background-color:#6369ED; color:White; height:40px;">
                    <p class="card-text" style="font-size: 18px;">ตรวจสอบผู้สมัคร</p>
                </div>

                @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
                @endif

                <div class="card-body">
                    <table id="datatable" class="table table-bordered">

                        <tr>
                            <td>ชื่องาน</td>
                            <td>ชื่อผู้สมัคร</td>
                            <td>รายละเอียด</td>
                            <td style="text-align:center">สถานะ</td>
                            <td style="text-align:center">การกระทำ</td>
                        </tr>

                        @if(isset($mamber))
                        @foreach ($mamber as $datas)
                        <tr>
                            <td>{{ $datas->myjobs_name}}</td>
                            <td>{{ $datas->first_name}}</td>
                            <td>{{ $datas->dominant_language }}</td>

                            <td style="text-align:center"> <a type="button" href="{{ route('ent.ent_see_detail_history',$datas->history_id) }}" class="btn btn-primary">ดูรายละเอียด</a></td>
                            <td style="text-align:center">
                                <a href="{{route('approve' , $datas->myjobs_id)}}" class="btn btn-success">อนุมัติ</a>
                                <a href="{{route('reject',$datas->myjobs_id)}}" class="btn btn-danger">ปฏิเสธ</a>
                                <a href="{{route('save_file',$datas->myjobs_id)}}" class="btn btn-info" role="button">เพิ่มลงในแฟ้ม</a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <br>

    <!-- บันทึกลงแฟ้ม -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="card" style="width: 100%;">
                <div class="card-header" style="background-color:#6369ED; color:White;height:40px;">
                    <p class="card-text" style="font-size: 18px;">แฟ้มที่บันทึก</p>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">

                        <tr>
                            <td>ชื่องาน</td>
                            <td>ชื่อผู้สมัคร</td>
                            <td>รายละเอียด</td>
                            <td style="text-align:center">สถานะ</td>
                            <td style="text-align:center">การกระทำ</td>
                        </tr>
                        @if(isset($file_save))
                        @foreach($file_save as $rows)
                        <tr>
                            <td>{{ $rows->myjobs_name}}</td>
                            <td>{{ $rows->first_name}}</td>
                            <td>{{ $rows->dominant_language }}</td>

                            <td style="text-align:center"> <a type="button" href="{{ route('ent.ent_see_detail_history',$rows->history_id) }}" class="btn btn-primary">ดูรายละเอียด</a></td>
                            <td style="text-align:center">
                                <a href="{{route('approve' , $rows->myjobs_id)}}" class="btn btn-success">อนุมัติ</a>
                                <a href="{{route('reject',$rows->myjobs_id)}}" class="btn btn-danger">ปฏิเสธ</a>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
            </div>

            <div class="card" style="width: 100%; margin-top: 30px;">
                <div class="card-header" style="background-color:#6369ED; color:White;height:40px;">
                    <p class="card-text" style="font-size: 18px;">รายชื่อคนที่อนุมัติ</p>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">

                        <tr>
                            <td>ชื่องาน</td>
                            <td>ชื่อผู้สมัคร</td>
                            <td>รายละเอียด</td>
                            <td style="text-align:center">สถานะ</td>
                        </tr>
                        @if(isset($rove))
                        @foreach($rove as $rows)
                        <tr>
                            <td>{{ $rows->myjobs_name}}</td>
                            <td>{{ $rows->first_name}}</td>
                            <td>{{ $rows->dominant_language }}</td>

                            <td style="text-align:center"> <a type="button" href="{{ route('ent.ent_see_detail_history',$rows->history_id) }}" class="btn btn-primary">ดูรายละเอียด</a></td>
                            <td class="text-center" style="color: green">อนุมัติเรียบร้อย</td>
                        </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function addmise(id) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET", "/Ent/addmiseappli?id=" + id, true);
            xmlhttp.send();
        }
    </script>
</body>
@endsection