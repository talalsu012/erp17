<div>
    <div class="container mx-auto py-2">
        <!-- Header -->

        <!-- Body -->
        <main class="mt-6 bg-white rounded-lg shadow-md p-6">

            <livewire:res.print-header />


            <table class="min-w-full divide-y divide-gray-200 border-collapse border border-slate-400 ">
                <thead class="bg-gray-100">
                    <tr>
                        @foreach($PrintHeaderTable as $columnField)
                        <th class="py-3 px-6 text-sm font-medium text-gray-600 uppercase">
                            {{ __('reg.'.$columnField) }}
                        </th>
                        @endforeach
                        @foreach($CoursePrint as $columnSubject)
                        <th class="py-3 px-6 text-sm font-medium text-gray-600 uppercase">
                            {{ $columnSubject->name_ar }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Example Row -->

                    @foreach($PrintDataTable as $columnStudent)
                    <tr>
                        @foreach($PrintHeaderTable as $columnField)
                        <td class="py-4 px-6 text-gray-700">{{ $columnStudent->$columnField }}</td>
                        @endforeach

                        @foreach($CoursePrint as $columnSubject)
                        @foreach($this->getMarkData($columnStudent->university_no,$columnSubject->code) as $Mark)
                        <td class="border border-gray-300">

                            <div class="grid grid-rows-3 grid-flow-col gap-2">
                                <div class="col-span-3">{{ $Mark->mark }} </div>
                                <div class="col-span-2">{{ $Mark->point }}</div>
                                <div class="col-span-1">{{ $Mark->grade }}</div>
                            </div>

                        </td>
                        @endforeach
                        @endforeach

                    </tr>
                    @endforeach

                    <!-- Repeat rows as needed -->
                </tbody>
            </table>
        </main>

        <!-- Footer -->
        <footer class="mt-6 bg-black-800 text-black py-4 px-6 rounded-lg shadow-md">
            <p class="text-sm">&copy; {{ date('Y') }} Exam Report System. All Rights Reserved.</p>
        </footer>
    </div>
</div>
