<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="container mx-auto px-4">
        <sx:set var="$testd" value="[
            [
                'label' => '1',
                'url' => 'url1'
            ],
            [
                'label' => '2',
                'url' => 'url2'
            ]
        ]" />
        <sx:ui-actions:dropdown label="..." items="$testd" />

        <div class="mt-3">
            <sx:ui-dataDisplay:alert type="info" message="12 unread messages. Tap to see." />
        </div>
    </div>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-lg sm:text-xl md:text-2xl">
                    SynapseUI Paneli
                </h2>
                <p class="text-sm sm:text-base">
                    Bu bölüm mobil uyumlu ve DaisyUI bileşenleri ile biçimlendirilmiştir.
                </p>
                <div class="card-actions justify-end mt-4">
                    <sx:ui-actions:button text="kaydet" variant="success" type="submit" class="btn-soft w-full" />

                    <sx:ui-actions:button text="modal" variant="primary" modal="modal_1" />
                    <sx:ui-actions:modal id="modal_1" button_text="Open Modal" title="test modal"
                        content="test modal window" size="modal-bottom sm:modal-middle" />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl mt-5">
            <div class="card-body">
                <sx:ui-fields:form method="post" inputs="$form.test" />
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl mt-5">
            <div class="card-body">
                <sx:ui-fields:form method="post" inputs="$form.test2" grid="true" />
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl mt-5">
            <div class="card-body">
                <sx:ui-dataDisplay:accordion items="$accordion" />
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl mt-5">
            <div class="card-body">
                <sx:ui-dataDisplay:table head="$table.head" body="$table.body" />
            </div>
        </div>
    </div>
</sx:block>