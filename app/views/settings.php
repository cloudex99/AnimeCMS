<?php include 'header.php' ?>

<form method='post' action='/settings' class="bg-light p-3 mb-5 w-100">
    <?php
    if(isset($_SESSION['msg'])){
        $msg = $_SESSION['msg'];
        unset($_SESSION['msg']);
        echo "<div class='alert alert-info' role='alert'>$msg</div>";
    }
    ?>

    <h3 class="mb-2">Settings</h3>
    <?php
        $settings = Config::get();
        foreach ($settings as $key => $value){
            if(!is_array($value)){
                $label = ucwords(str_replace(['-','_'],' ', $key));
                $type = 'text';
                $placeholder = '';
                $readonly = '';
                if ($key === 'admin_password' || $key === 'admin_username')
                    continue;
                /*
                    $type = 'password';
                if ($key === 'admin_username')
                    $readonly = 'readonly';
                */
                $input = "<input type='text' class='form-control form-control-sm' placeholder='$key' type='$type' name=$key value='$value' $readonly>";
                if ($key === 'desc') {
                    $input = "<textarea name=$key class='form-control form-control-sm' placeholder='$key'>$value</textarea>";
                }
                echo "<div class='form-group row'>
                    <label for='$key' class='col-sm-2 col-form-label col-form-label-sm'>$label</label>
                    <div class='col-sm-10'>
                      $input
                    </div>
                  </div>";
            }
        }
    ?>
    <hr>
    <h5 class="my-2">Anime Page</h5>
    <?php
    foreach ($settings['anime'] as $key => $value){
        $label = ucwords(str_replace(['-','_'],' ', $key));
        $name = "anime[$key]";
        $input = "<input name=$name type='text' class='form-control form-control-sm' placeholder='$key' value='$value'>";
        echo "<div class='form-group row mb-0'>";
        echo "<label class='col-sm-2 col-form-label col-form-label-sm'>$label</label>";
        echo "<div class='col-sm-10 ml-auto my-1'>$input</div>";
        echo "</div>";
    }
    ?>
    <hr>
    <h5 class="my-2">Episode Page</h5>
    <?php
    foreach ($settings['episode'] as $key => $value){
        $label = ucwords(str_replace(['-','_'],' ', $key));
        $name = "episode[$key]";
        $input = "<input name=$name type='text' class='form-control form-control-sm' placeholder='$key' value='$value'>";
        echo "<div class='form-group row mb-0'>";
        echo "<label class='col-sm-2 col-form-label col-form-label-sm'>$label</label>";
        echo "<div class='col-sm-10 ml-auto my-1'>$input</div>";
        echo "</div>";
    }
    ?>
    <hr>
    <h5 class="my-2">Cartoon Page</h5>
    <?php
    foreach ($settings['cartoon'] as $key => $value){
        $label = ucwords(str_replace(['-','_'],' ', $key));
        $name = "cartoon[$key]";
        $input = "<input name=$name type='text' class='form-control form-control-sm' placeholder='$key' value='$value'>";
        echo "<div class='form-group row mb-0'>";
        echo "<label class='col-sm-2 col-form-label col-form-label-sm'>$label</label>";
        echo "<div class='col-sm-10 ml-auto my-1'>$input</div>";
        echo "</div>";
    }
    ?>
    <hr>
    <h5 class="my-2">Cartoon Episode Page</h5>
    <?php
    foreach ($settings['cartoon_episode'] as $key => $value){
        $label = ucwords(str_replace(['-','_'],' ', $key));
        $name = "cartoon_episode[$key]";
        $input = "<input name=$name type='text' class='form-control form-control-sm' placeholder='$key' value='$value'>";
        echo "<div class='form-group row mb-0'>";
        echo "<label class='col-sm-2 col-form-label col-form-label-sm'>$label</label>";
        echo "<div class='col-sm-10 ml-auto my-1'>$input</div>";
        echo "</div>";
    }
    ?>
    <hr>
    <h5 class="my-2">Other Pages</h5>
    <?php
        foreach ($settings['pages'] as $page_key => $page_options){
            $page_num = $page_key+1;
            foreach ($page_options as $key => $value) {
                $label = ucwords(str_replace(['-','_'],' ', $key));
                $name = "pages[$page_key][$key]";
                $input = "<input name=$name type='text' class='form-control form-control-sm' placeholder='$key' value='$value'>";
                if ($key === 'desc') {
                    $input = "<textarea name=$name class='form-control form-control-sm' placeholder='$key'>$value</textarea>";
                }
                echo "<div class='form-group row mb-0'>";
                echo "<label class='col-sm-2 col-form-label col-form-label-sm'>$label</label>";
                echo "<div class='col-sm-10 ml-auto my-1'>$input</div>";
                echo "</div>";
            }
            echo "<hr>";
        }

    ?>

    <button class="btn btn-primary m-2 float-right" type="submit">Update</button>
    <button class="btn btn-danger m-2 float-right" type="submit" formaction="/settings/reload">Reload</button>
    <button class="btn btn-warning m-2 float-right" type="submit" formaction="/settings/sitemap">Generate Sitemap</button>
    <div class="clearfix"></div>
</form>

<?php include 'footer.php' ?>