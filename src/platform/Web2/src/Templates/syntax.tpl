<sx:extends template="main.tpl" />

<sx:block name="maindd">
    <h1>Welcome to Par3 own template system</h1>

    <h2>Template (name, path)</h2>
    <p>Name: {$this.name}</p>
    <p>Path: {$this.path}</p>

    <h2>Block (sx:block, sx:extends)</h2>
    <p>This page is have block.</p>

    <h2>Comments (sx:comment)</h2>
    <sx:comment>No echo</sx:comment>
    <p>(invisible)</p>

    <h2>Raw Block (sx:raw)</h2>
    <sx:raw>
        <p>{$user.bio}</p>
    </sx:raw>

    <h2>Variable</h2>
    <p>{$foo}</p>
    <p>Function: {$fooFunction.test($foo, 'hi', 0)|ucwords} (Support args and filter system)</p>
    <p>Object: {$fooObj->bar->baz} (You can use '->' or '.')</p>
    <p>If variable is not exist, its invisible. No output error. {$x}</p>
    <p>If no curly bracket: $foo</p>
    {$user->name}

    <h2>Condition (sx:if / sx:elseif / sx:else)</h2>
    <sx:if is="$user.loggedIn">
        <p>Welcome, {$user.name}</p>
        <sx:if is="$user.age >= 18 && ($user.verified || $user.admin)">
            <p>Welcome, adult!</p>
        <sx:elseif is="$user.age >= 13 && $user.parentApproved" />
            <p>Teen access granted.</p>
        <sx:else />
            <p>Access denied.</p>
        </sx:if>
        <sx:else />
        <p>Please login.</p>
    </sx:if>

    <h2>Loop (sx:foreach)</h2>
    <ol>
        <sx:foreach loop="$items" key="$i" value="$item">
            <li>{$i}: {$item}</li>
        </sx:foreach>
    </ol>

    <h2>Macro (sx:macro and sx:call. Note: You can also use it in sx:block if you want, but we do not recommend it.)</h2>
    <sx:macro name="userCard" user="$user">
        <div class="user-card">
            <h3>{$user.name|ucwords}</h3>
            <p>Age: {$user.age}</p>
        </div>
    </sx:macro>
    <sx:call macro="userCard" user="$user" />

    <h2>Set (sx:set)</h2>
    <sx:set var="$welcome" value="Welcome {$user.name}" />
    <p>{$welcome}</p>

    <h2>Include (sx:include)</h2>
    <sx:include var="test_template" template="test.tpl" />
    {$test_template}

    <h2>Dump (filter dump veya sy-dump)</h2>
    {$user|sy-dump}

    <h2>Filters (capitalize, lower, ucwords)</h2>
    <ol>
        <li>{$foo|capitalize}</li>
        <li>{$foo|lower}</li>
        <li>{$foo|ucwords}</li>
    </ol>
</sx:block>