<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="d-flex vh-100 vw-100">
        <div class="side d-flex flex-column flex-shrink-0 text-white">
            <div class="head d-flex justify-content-between align-items-center px-3 my-3 fs-lg">
                <div>[logo]</div>
                <div js-ref="side-toggle">
                    <a class="link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover"
                        href="#"><i class="bi bi-layout-sidebar"></i></a>
                </div>
            </div>
            <div
                class="interactive-menu d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <span class="text-muted">(Interactive menu)</span>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#"><i class="bi bi-bricks"></i>
                        [chat builder]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative mt-3">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#"><i
                            class="bi bi-pencil-square"></i> [new chat]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#"><i class="bi bi-search"></i>
                        [search chat]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#"><i class="bi bi-archive"></i>
                        [archives]</a>
                </div>
            </div>
            <div class="chat-list d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <span class="text-muted">(Chat area)</span>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#">[chat 1]</a>
                    <div class="ms-auto">
                        <span class="badge text-bg-primary ms-1">gemma3:1b</span>
                        <span class="badge text-bg-secondary ms-1">chat</span>
                        <span class="badge text-bg-warning ms-1">vision</span>
                    </div>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#">[chat 2]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#">[chat 3]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#">[chat 4]</a>
                </div>
                <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                    <a class="link-light link-offset-2 link-underline-opacity-0" href="#">[chat 5]</a>
                </div>
            </div>
        </div>
        <div class="main d-flex flex-column flex-shrink-1 border-start border-dark-subtle w-100 h-100 fs-lg">
            <div class="d-flex flex-column align-items-start justify-content-start px-3 my-3 h-35">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>[model selector]</div>
                    <div>[archive or anonymous chat]</div>
                </div>
                <div class="d-flex align-items-center justify-content-center mt-auto w-100 fs-4">
                    <div>[Chat starting area]</div>
                </div>
            </div>
        </div>
    </div>
</sx:block>