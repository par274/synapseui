<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="container mx-auto p-4 mt-10">
        <h1 class="text-center text-3xl font-bold mb-6">Giriş Yap</h1>

        <div class="max-w-md mx-auto bg-base-100 p-8 rounded-lg shadow-md">
            <div class="flex justify-center gap-4 mb-8 flex-wrap">
                <sx:ui-actions:button text="GitHub" variant="success" type="button" class="btn btn-outline mx-1 mb-4 sm:mb-0" />
                <sx:ui-actions:button text="Google" variant="info" type="button" class="btn btn-outline mx-1 mb-4 sm:mb-0" />
            </div>
            <sx:ui-fields:form method="post" inputs="$forms.login" />
        </div>
    </div>
</sx:block>