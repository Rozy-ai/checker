<?php
/**
 * @var string $f_asin_multiple 
 */
$filtersOpened = !empty($f_asin_multiple);
?>

<div class="form-row mt-3 mb-3">
    <div class="col col-12 d-flex justify-content-end flex-wrap">
        <a сlass="btn btn-link d-flex align-items-center" href="#" id="additional_filter_link">
            Расширенный фильтр
            <i class="bi bi-caret-down-fill"></i>
        </a>
        <div class="w-100 additional-filters" class="pt-2"<?php echo !$filtersOpened ? 'style="display: none;"' : ''; ?>>
            <div class="row">
                <div class="col col-12 col-md-4 col-lg-3">
                    <textarea type="text" class="form-control" placeholder="ASIN (multiple)" id="id_f_asin_multiple" name="f_asin_multiple" rows="2"
                    ><?php echo $f_asin_multiple ?: ""; ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>