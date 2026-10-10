                {!! Form::open(['url' => 'fetch/new/' . $fetch->id]) !!}

                <p>This will submit the fetch quest, remove one {!! $fetch->fetchItem->displayName !!}, and add currency to your account. Are
                    you sure?</p>

                <div class="text-right">
                    {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
                </div>

                {!! Form::close() !!}
