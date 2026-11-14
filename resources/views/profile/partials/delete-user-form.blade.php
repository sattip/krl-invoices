<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Διαγραφή λογαριασμού
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Με τη διαγραφή του λογαριασμού σας διαγράφονται οριστικά ο λογαριασμός χρήστη και τα παραστατικά που έχετε ανεβάσει εσείς. Διαγράφονται επίσης τα αρχεία αυτών των παραστατικών και τα API tokens σας. Η εταιρεία, οι υπόλοιποι χρήστες, τα παραστατικά τους και η συνδρομή δεν διαγράφονται· η συνδρομή δεν ακυρώνεται αυτόματα. Για πλήρη διαγραφή της εταιρείας επικοινωνήστε στο support@obstechnologies.com. Πριν από τη διαγραφή, κατεβάστε όσα δεδομένα θέλετε να διατηρήσετε.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Διαγραφή λογαριασμού</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Είστε βέβαιοι ότι θέλετε να διαγράψετε τον λογαριασμό σας;
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Η ενέργεια είναι οριστική και δεν αναιρείται. Πληκτρολογήστε τον κωδικό πρόσβασής σας για επιβεβαίωση.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Κωδικός πρόσβασης" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Κωδικός πρόσβασης"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Άκυρο
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Οριστική διαγραφή λογαριασμού
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
