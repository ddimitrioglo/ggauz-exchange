<?php
/*
Template Name: MG Exchange [Template]
*/

require_once(ABSPATH . 'wp-content/plugins/ggauz-exchange/includes/GgauzExchange.php');
$mge = new GgauzExchange();
$dataSet = $mge->getLastDataSet();
$mdlValue = $mge->getDefaultValue();
$chartCurrency = $mge->getDefaultChartCurrency();

get_header(); ?>

<div id="primary" class="mge content-area">
    <main id="main" class="site-main" role="main">
        <article class="page type-page status-publish hentry">
            <div class="entry-content">
                <table>
                    <tr>
                        <td>MDL</td>
                        <td>1.0000</td>
                        <td><small>-</small></td>
                        <td>
                            <input id="mdl" class="currency"
                                   type="number" value="<?php echo $mdlValue; ?>" data-rate="1" >
                        </td>
                    </tr>
                    <?php foreach ($dataSet as $data) : ?>
                        <tr class="mge-currency">
                            <td><?php echo $data['symb']; ?></td>
                            <td><?php echo $data['rate']; ?></td>
                            <td>
                                <small class="<?php if ($data['diff'] > 0): ?>green <?php else : ?>red<?php endif; ?>">
                                    <?php echo $data['diff']; ?>
                                </small>
                            </td>
                            <td>
                                <input id="<?php echo strtolower($data['symb']); ?>"
                                       class="currency"
                                       type="number"
                                       value="<?php echo round($mdlValue/$data['rate'], 3); ?>"
                                       data-rate="<?php echo $data['rate']; ?>" >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div>
                    <?php foreach ($dataSet as $data) : ?>
                        <label>
                            <input type="radio"
                                   name="currency-code"
                                    <?php if ($chartCurrency == $data['code']) :?>
                                        checked="checked"
                                    <?php endif; ?>
                                   class="currency-code"
                                   data-code="<?php echo $data['code']; ?>">
                            <?php echo $data['symb']; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="mge-chart"></div>
            </div>
        </article>
    </main>
</div>

<script>
    var chartValues = '<?php echo $mge->getDataSetForChart(); ?>';
</script>

<?php get_footer(); ?>
