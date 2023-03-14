<?php

/**
 * @var string $f_asin_multiple 
 * @var string|integer $f_new
 * @var string|integer $f_favor
 */
$filtersOpened = !empty($f_asin_multiple) || (int)$f_new || (int)$f_favor;
?>

<div class="form-row mt-3 mb-3">
    <div class="col col-12 d-flex justify-content-end flex-wrap">
        <a сlass="btn btn-link d-flex align-items-center" href="#" id="additional_filter_link">
            Расширенный фильтр
            <i class="bi bi-caret-down-fill"></i>
        </a>
        <div class="w-100 additional-filters" class="pt-2" <?php echo !$filtersOpened ? 'style="display: none;"' : ''; ?>>
            <div class="row">
                <div class="col col-12 col-md-4 col-lg-3">
                    <textarea type="text" class="form-control" placeholder="ASIN (multiple)" id="id_f_asin_multiple" name="f_asin_multiple" rows="2"><?php echo $f_asin_multiple ?: ""; ?></textarea>
                </div>
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control h-auto" id="id_f_new" name="f_new" <?php echo (int)$f_new ? ' checked' : ''; ?> />
                        <label for="id_f_new" class="mb-0 ml-2">New</label>
                    </div>
                </div>
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control h-auto" id="id_f_favor" name="f_favor" <?php echo (int)$f_favor ? ' checked' : ''; ?> />
                        <label for="id_f_new" class="mb-0 ml-2">Favorites</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>