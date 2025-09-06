<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="d-flex vh-100 vw-100">
        <div class="side d-flex flex-column flex-shrink-0 text-white expanded">
            <div class="border-end border-dark-subtle">
                <div class="head d-flex justify-content-center align-items-center px-3 my-3 fs-lg">
                    <div class="label">[logo]</div>
                    <div js-tooltip="true"
                        class="side-link d-flex align-items-center px-2 py-1 ms-auto position-relative">
                        <a js-ref="side-toggle" class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label d-none">{$app.translator->trans('toggle.text', [], 'sidebar')}</span>
                            <i class="bi bi-layout-sidebar"></i>
                            <div class="js-ref"></div>
                        </a>
                    </div>
                </div>
                <div
                    class="interactive-menu d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                    <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <span class="label text-muted">(Interactive menu)</span>
                    </div>
                    <div js-tooltip="true"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a js-ref="tab" js-tab-href="chat-builder"
                            class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-bricks"></i>
                            <span class="label">[chat builder]</span>
                            <div class="js-ref"></div>
                        </a>
                    </div>
                    <div js-tooltip="true"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative mt-3 active">
                        <a js-ref="tab" js-tab-href="new-chat" class="link-light link-offset-2 link-underline-opacity-0"
                            href="#">
                            <i class="bi bi-pencil-square"></i>
                            <span class="label">[new chat]</span>
                            <div class="js-ref"></div>
                        </a>
                    </div>
                    <div js-tooltip="true"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-search"></i>
                            <span class="label">[search chat]</span>
                        </a>
                    </div>
                    <div js-tooltip="true"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-archive"></i>
                            <span class="label">[archives]</span>
                        </a>
                    </div>
                </div>
                <div class="chat-list d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                    <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <span class="label text-muted">(Chat area)</span>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 1]</span>
                        </a>
                        <div class="label ms-auto">
                            <span class="badge text-bg-primary ms-1">gemma3:1b</span>
                            <span class="badge text-bg-secondary ms-1">chat</span>
                            <span class="badge text-bg-warning ms-1">vision</span>
                        </div>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 2]</span>
                        </a>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 3]</span>
                        </a>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 4]</span>
                        </a>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 5]</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main w-100">
            <div
                class="new-chat d-flex flex-column flex-shrink-1 w-100 h-100 fs-lg active">
                <div class="d-flex flex-column align-items-start justify-content-start px-3 my-3 h-35">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div>[model selector]</div>
                        <div>[archive or anonymous chat]</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center mt-auto w-100 fs-4">
                        <div>[Chat starting area]</div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-center mx-auto mt-5 w-100">
                    <div class="chat-root w-50"></div>
                </div>
            </div>
            <div
                class="chat-builder d-flex flex-column flex-shrink-1 w-100 h-100 fs-lg d-none">
                <div class="px-3 my-3">OK</div>
            </div>
        </div>
    </div>
</sx:block>