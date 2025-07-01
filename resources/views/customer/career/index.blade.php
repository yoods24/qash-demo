<x-customer.layout>
    <section>
        <div class="section-wrapper text-black">
            <h1>Career</h1>
            <div class="d-flex">
                <div class="container text-center">
                    <div class="row row-cols-2 row-cols-md-2 g-3"> <!-- g-3 adds gutter between rows & cols -->
                        @foreach ($careers as $career)
                            <x-customer.career-card-wide :$career />
                        @endforeach
                    </div>
                    {{$careers->links()}}
                </div>
            </div>
        </div>
    </section>
</x-customer.layout>
