<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <table>
            <tr>
                <td>ID: {{$reports->id}}</td>
                <td>Date: {{$reports->start_date}} - {{$reports->end_date}}</td>
                <td>Misc</td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Code</th>
                    <th>Quantity</th>
                    <th>Description</th>
                    <th>Gross</th>
                    <th>Discounts</th>
                    <th>Net</th>
                </tr>
            </thead>
            <tbody>
            @foreach($misc as $trans)
            <tr>
                <td>{{$trans['processed_at']}}</td>
                <td>{{$trans['code']}}</td>
                <td>{{$trans['quantity']}}</td>
                <td>{{$trans['description']}}</td>
                <td>{{$trans['gross']}}</td>
                <td>{{$trans['discounts']}}</td>
                <td>{{$trans['net']}}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    
    </body>
</html> 