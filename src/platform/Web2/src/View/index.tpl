<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="d-flex vh-100 vw-100">
        <div class="side d-flex flex-column flex-shrink-0 expanded">
            <div>
                <div class="head d-flex justify-content-center align-items-center px-3 my-3 fs-normal">
                    <div class="label">[logo]</div>
                    <div js-ref="side-toggle" js-tooltip="true" js-tooltip-placement="right" js-tooltip-offset="[0, 15]"
                        class="side-link d-flex align-items-center px-2 ms-auto position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label d-none">{$app.translator->trans('toggle.text', [], 'sidebar')}</span>
                            <i class="bi bi-layout-sidebar"></i>
                        </a>
                    </div>
                </div>
                <div
                    class="interactive-menu d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                    <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <span class="label text-muted">{$app.translator->trans('interactive.menu.text', [], 'sidebar')}</span>
                    </div>
                    <div js-tooltip="true" js-tooltip-rule="collapsed" js-tooltip-placement="right" js-tooltip-offset="[0, 15]"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a js-ref="tab" js-tab-href="chat-builder"
                            class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-bricks"></i>
                            <span class="label">{$app.translator->trans('interactive.menu.chat_builder', [], 'sidebar')}</span>
                        </a>
                    </div>
                    <div js-tooltip="true" js-tooltip-rule="collapsed" js-tooltip-placement="right" js-tooltip-offset="[0, 15]"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative mt-3 active">
                        <a js-ref="tab" js-tab-href="new-chat" class="link-light link-offset-2 link-underline-opacity-0"
                            href="#">
                            <i class="bi bi-pencil-square"></i>
                            <span class="label">{$app.translator->trans('new_chat', [], 'sidebar')}</span>                            
                        </a>
                    </div>
                    <div js-tooltip="true" js-tooltip-rule="collapsed" js-tooltip-placement="right" js-tooltip-offset="[0, 15]"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-search"></i>
                            <span class="label">{$app.translator->trans('search_chat', [], 'sidebar')}</span>
                        </a>
                    </div>
                    <div js-tooltip="true" js-tooltip-rule="collapsed" js-tooltip-placement="right" js-tooltip-offset="[0, 15]"
                        class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <i class="bi bi-archive"></i>
                            <span class="label">{$app.translator->trans('archives', [], 'sidebar')}</span>
                        </a>
                    </div>
                </div>
                <div class="chat-list d-flex flex-column justify-content-center align-items-start px-2 my-3 gap-1 mt-3">
                    <div class="side-head d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <span class="label text-muted">{$app.translator->trans('chat.area.text', [], 'sidebar')}</span>
                    </div>
                    <div class="side-link d-flex align-items-center px-2 py-1 w-100 position-relative">
                        <a class="link-light link-offset-2 link-underline-opacity-0" href="#">
                            <span class="label">[chat 1]</span>
                        </a>
                        <div class="label ms-auto">
                            <span class="badge text-bg-primary fw-semibold ms-1">gemma3:1b</span>
                            <span class="badge text-bg-secondary fw-semibold ms-1">chat</span>
                            <span class="badge text-bg-warning fw-semibold ms-1">vision</span>
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
                <div class="head d-flex flex-column align-items-start justify-content-start h-35">
                    <div class="position-fixed">
                        <div class="d-flex align-items-center justify-content-between px-3 pt-1 my-3 w-100">
                            <div>
                                <div class="dropdown">
                                    <a class="label link-highlighted link-light link-offset-2 link-underline-opacity-0" href="#" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        {$app.translator->trans('model_selector.dropdown.text', [], 'chat')}
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <span class="d-block text-muted fs-normal">Head</span>
                                        </li>
                                        <li>
                                            <a class="link-highlighted link-light link-offset-2 link-underline-opacity-0 d-block" href="#">Action</a>
                                        </li>
                                        <li>
                                            <a class="link-highlighted link-light link-offset-2 link-underline-opacity-0 d-block" href="#">Another action</a>
                                        </li>
                                        <li>
                                            <a class="link-highlighted link-light link-offset-2 link-underline-opacity-0 d-block" href="#">Something else here</a>
                                        </li>
                                        <div class="line"></div>
                                        <li>
                                            <span class="d-block text-muted fs-normal">Another head 2</span>
                                        </li>
                                        <li>
                                            <a class="link-highlighted link-light link-offset-2 link-underline-opacity-0 d-block" href="#">Another action</a>
                                        </li>
                                        <li>
                                            <a class="link-highlighted link-light link-offset-2 link-underline-opacity-0 d-block" href="#">Something else here</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <a js-tooltip="true" class="link-highlighted link-light link-offset-2 link-underline-opacity-0" href="#">
                                    <i class="bi bi-eye"></i>
                                    <span class="label d-none">{$app.translator->trans('anonymous_chat.text', [], 'chat')}</span>
                                </a>
                            </div>
                        </div>
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