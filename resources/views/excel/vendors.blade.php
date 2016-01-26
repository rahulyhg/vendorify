<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body> 
        <table>
            <thead>
                <tr>
                    <th>name</th>
                    <th>business</th>
                    <th>codes</th>
                    <th>phone</th>
                    <th>status</th>
                    <th>rent</th>
                    <th>rate</th>
                    <th>notify</th>
                    <th>email</th>
                </tr>
            </thead>
            <tbody>
            @foreach($vendors as $vendor)
                <tr>
                    <td>{{$vendor['name']}}</td>
                    <td>{{$vendor['business']}}</td>
                    <td>
                        @foreach($vendor['codes'] as $code)
                            {{$code->name}}  
                        @endforeach
                    </td>
                    <td>{{$vendor['phone']}}</td>
                    <td>{{$vendor['status']}}</td>
                    <td>{{$vendor['rent']}}</td>
                    <td>{{$vendor['rate']}}</td>
                    <td>{{$vendor['notify'] ? 'Yes' : 'No'}}</td>
                    <td>{{$vendor['email']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>