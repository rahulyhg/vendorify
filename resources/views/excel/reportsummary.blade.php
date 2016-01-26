<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>

        <table>
            <tr>
                <td>ID: {{$reports->id}}</td>
                <td>Date: {{$reports->start_date}} - {{$reports->end_date}}</td>
                <td><strong>Payout: {{ round($data['payout'], 2) }}</strong></td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Business</th>
                    <th>Name</th>
                    <th>Gross</th>
                    <th>Discounts</th>
                    <th>Net</th>
                    <th>Commision</th>
                    <th>Total</th>
                    @if($reports->include_rent)
                        <th>Rent</th>
                    @endif
                    <th>Payout</th>
                    <th>Check #</th>
                </tr>
            </thead>
            <tbody>
            @foreach($data['vendors'] as $report)
            <tr>
                <td>{{ $report['vendor']['email'] }}</td>
                <td>{{$report['vendor']['business']}}</td>
                <td>{{$report['vendor']['name']}}</td>
                <td>{{ round($report['gross'], 2) }}</td>
                <td>{{ round($report['discounts'], 2) }}</td>
                <td>{{ round($report['net'], 2) }}</td>
                <td>{{ round($report['commision'], 2) }}</td>
                <td>{{ round($report['total'], 2) }}</td>
                @if($reports->include_rent)
                    <td>{{ $report['rent']}}</td>
                @endif
                <td>{{ round($report['payout'], 2) }}</td>
                <td></td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr></tr>
                <tr style="text-align:right;">
                    <td></td>
                    <td></td>
                    <td><strong>Total:</strong></td>
                    <td><strong>{{ round($data['gross'], 2) }}</strong></td>
                    <td><strong>{{ round($data['discounts'], 2) }}</strong></td>
                    <td><strong>{{ round($data['net'], 2) }}</strong></td>
                    <td><strong>{{ round($data['commision'], 2) }}</strong></td>
                    <td><strong>{{ round($data['total'], 2) }}</strong></td>
                    @if($reports->include_rent)
                        <td><strong>{{$data['rent']}}</strong></td>
                    @endif
                    <td><strong>{{ round($data['payout'], 2) }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
            </tbody>
        </table>

    </body>
</html> 