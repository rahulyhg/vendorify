<p><strong>Congratulations! You just sold the following:</strong><p>

<ul>
    @foreach ($transactions as $transaction)
        <li>
            {{$transaction->quantity}} x {{$transaction->description}} {{$transaction->refund ? '(refunded)' : '' }} - ${{number_format($transaction->total, 2)}}
            
        </li>
    @endforeach
</ul>
