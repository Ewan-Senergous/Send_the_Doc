<?php

// Préparation des données pour le JavaScript
$simulateurData = array(
    'puissancesMoteur' => array(0.12, 0.18, 0.20, 0.25, 0.37, 0.4, 0.55, 0.75,
        1.1, 1.5, 2.2, 3, 4, 5.5, 7.5, 11, 15, 18.5, 22,
        30, 37, 45, 55, 75, 90, 110, 132, 160, 200, 250,
        315, 355, 400, 450, 500, 1000),
    'rendements' => array(
        "IE1" => array(
            0.12 => array('600_900' => 0.310, '901_1200' => 0.383, '1201_1800' => 0.500, '1801_6000' => 0.450),
            0.18 => array('600_900' => 0.380, '901_1200' => 0.455, '1201_1800' => 0.570, '1801_6000' => 0.528),
            0.20 => array('600_900' => 0.397, '901_1200' => 0.476, '1201_1800' => 0.585, '1801_6000' => 0.546),
            0.25 => array('600_900' => 0.434, '901_1200' => 0.521, '1201_1800' => 0.615, '1801_6000' => 0.582),
            0.37 => array('600_900' => 0.497, '901_1200' => 0.597, '1201_1800' => 0.660, '1801_6000' => 0.639),
            0.40 => array('600_900' => 0.509, '901_1200' => 0.611, '1201_1800' => 0.668, '1801_6000' => 0.649),
            0.55 => array('600_900' => 0.561, '901_1200' => 0.658, '1201_1800' => 0.700, '1801_6000' => 0.690),
            0.75 => array('600_900' => 0.612, '901_1200' => 0.700, '1201_1800' => 0.721, '1801_6000' => 0.721),
            1.1 => array('600_900' => 0.655, '901_1200' => 0.729, '1201_1800' => 0.750, '1801_6000' => 0.750),
            1.5 => array('600_900' => 0.702, '901_1200' => 0.752, '1201_1800' => 0.772, '1801_6000' => 0.772),
            2.2 => array('600_900' => 0.742, '901_1200' => 0.777, '1201_1800' => 0.797, '1801_6000' => 0.797),
            3 => array('600_900' => 0.770, '901_1200' => 0.797, '1201_1800' => 0.815, '1801_6000' => 0.815),
            4 => array('600_900' => 0.792, '901_1200' => 0.814, '1201_1800' => 0.831, '1801_6000' => 0.831),
            5.5 => array('600_900' => 0.814, '901_1200' => 0.831, '1201_1800' => 0.847, '1801_6000' => 0.847),
            7.5 => array('600_900' => 0.831, '901_1200' => 0.847, '1201_1800' => 0.860, '1801_6000' => 0.860),
            11 => array('600_900' => 0.850, '901_1200' => 0.864, '1201_1800' => 0.876, '1801_6000' => 0.876),
            15 => array('600_900' => 0.862, '901_1200' => 0.877, '1201_1800' => 0.887, '1801_6000' => 0.887),
            18.5 => array('600_900' => 0.869, '901_1200' => 0.886, '1201_1800' => 0.893, '1801_6000' => 0.893),
            22 => array('600_900' => 0.874, '901_1200' => 0.892, '1201_1800' => 0.899, '1801_6000' => 0.899),
            30 => array('600_900' => 0.883, '901_1200' => 0.902, '1201_1800' => 0.907, '1801_6000' => 0.907),
            37 => array('600_900' => 0.888, '901_1200' => 0.908, '1201_1800' => 0.912, '1801_6000' => 0.912),
            45 => array('600_900' => 0.892, '901_1200' => 0.914, '1201_1800' => 0.917, '1801_6000' => 0.917),
            55 => array('600_900' => 0.897, '901_1200' => 0.919, '1201_1800' => 0.921, '1801_6000' => 0.921),
            75 => array('600_900' => 0.903, '901_1200' => 0.926, '1201_1800' => 0.927, '1801_6000' => 0.927),
            90 => array('600_900' => 0.907, '901_1200' => 0.929, '1201_1800' => 0.930, '1801_6000' => 0.930),
            110 => array('600_900' => 0.911, '901_1200' => 0.933, '1201_1800' => 0.933, '1801_6000' => 0.933),
            132 => array('600_900' => 0.915, '901_1200' => 0.935, '1201_1800' => 0.935, '1801_6000' => 0.935),
            160 => array('600_900' => 0.919, '901_1200' => 0.938, '1201_1800' => 0.938, '1801_6000' => 0.938),
            200 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            250 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            315 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            355 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            400 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            450 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            500 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940),
            1000 => array('600_900' => 0.925, '901_1200' => 0.940, '1201_1800' => 0.940, '1801_6000' => 0.940)
        ),
        "IE2" => array(
            0.12 => array('600_900' => 0.398, '901_1200' => 0.506, '1201_1800' => 0.591, '1801_6000' => 0.536),
            0.18 => array('600_900' => 0.459, '901_1200' => 0.566, '1201_1800' => 0.647, '1801_6000' => 0.604),
            0.20 => array('600_900' => 0.474, '901_1200' => 0.582, '1201_1800' => 0.659, '1801_6000' => 0.619),
            0.25 => array('600_900' => 0.506, '901_1200' => 0.616, '1201_1800' => 0.685, '1801_6000' => 0.648),
            0.37 => array('600_900' => 0.561, '901_1200' => 0.676, '1201_1800' => 0.727, '1801_6000' => 0.695),
            0.40 => array('600_900' => 0.572, '901_1200' => 0.688, '1201_1800' => 0.735, '1801_6000' => 0.704),
            0.55 => array('600_900' => 0.617, '901_1200' => 0.731, '1201_1800' => 0.771, '1801_6000' => 0.741),
            0.75 => array('600_900' => 0.662, '901_1200' => 0.759, '1201_1800' => 0.796, '1801_6000' => 0.774),
            1.1 => array('600_900' => 0.708, '901_1200' => 0.781, '1201_1800' => 0.814, '1801_6000' => 0.796),
            1.5 => array('600_900' => 0.741, '901_1200' => 0.798, '1201_1800' => 0.828, '1801_6000' => 0.813),
            2.2 => array('600_900' => 0.776, '901_1200' => 0.818, '1201_1800' => 0.843, '1801_6000' => 0.832),
            3 => array('600_900' => 0.800, '901_1200' => 0.833, '1201_1800' => 0.855, '1801_6000' => 0.846),
            4 => array('600_900' => 0.819, '901_1200' => 0.846, '1201_1800' => 0.866, '1801_6000' => 0.858),
            5.5 => array('600_900' => 0.838, '901_1200' => 0.860, '1201_1800' => 0.877, '1801_6000' => 0.870),
            7.5 => array('600_900' => 0.853, '901_1200' => 0.872, '1201_1800' => 0.887, '1801_6000' => 0.881),
            11 => array('600_900' => 0.869, '901_1200' => 0.887, '1201_1800' => 0.898, '1801_6000' => 0.894),
            15 => array('600_900' => 0.880, '901_1200' => 0.897, '1201_1800' => 0.906, '1801_6000' => 0.903),
            18.5 => array('600_900' => 0.886, '901_1200' => 0.904, '1201_1800' => 0.912, '1801_6000' => 0.909),
            22 => array('600_900' => 0.891, '901_1200' => 0.909, '1201_1800' => 0.916, '1801_6000' => 0.913),
            30 => array('600_900' => 0.898, '901_1200' => 0.917, '1201_1800' => 0.923, '1801_6000' => 0.920),
            37 => array('600_900' => 0.903, '901_1200' => 0.922, '1201_1800' => 0.927, '1801_6000' => 0.925),
            45 => array('600_900' => 0.907, '901_1200' => 0.927, '1201_1800' => 0.931, '1801_6000' => 0.929),
            55 => array('600_900' => 0.910, '901_1200' => 0.931, '1201_1800' => 0.935, '1801_6000' => 0.932),
            75 => array('600_900' => 0.916, '901_1200' => 0.937, '1201_1800' => 0.940, '1801_6000' => 0.938),
            90 => array('600_900' => 0.919, '901_1200' => 0.940, '1201_1800' => 0.942, '1801_6000' => 0.941),
            110 => array('600_900' => 0.923, '901_1200' => 0.943, '1201_1800' => 0.945, '1801_6000' => 0.943),
            132 => array('600_900' => 0.926, '901_1200' => 0.946, '1201_1800' => 0.947, '1801_6000' => 0.946),
            160 => array('600_900' => 0.930, '901_1200' => 0.948, '1201_1800' => 0.949, '1801_6000' => 0.948),
            200 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            250 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            315 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            355 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            400 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            450 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            500 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950),
            1000 => array('600_900' => 0.935, '901_1200' => 0.950, '1201_1800' => 0.951, '1801_6000' => 0.950)
        ),
        "IE3" => array(
    0.12 => array('600_900' => 0.507, '901_1200' => 0.577, '1201_1800' => 0.648, '1801_6000' => 0.608),
    0.18 => array('600_900' => 0.587, '901_1200' => 0.639, '1201_1800' => 0.699, '1801_6000' => 0.659),
    0.20 => array('600_900' => 0.606, '901_1200' => 0.654, '1201_1800' => 0.711, '1801_6000' => 0.672),
    0.25 => array('600_900' => 0.641, '901_1200' => 0.686, '1201_1800' => 0.735, '1801_6000' => 0.697),
    0.37 => array('600_900' => 0.693, '901_1200' => 0.735, '1201_1800' => 0.773, '1801_6000' => 0.738),
    0.40 => array('600_900' => 0.701, '901_1200' => 0.744, '1201_1800' => 0.780, '1801_6000' => 0.746),
    0.55 => array('600_900' => 0.730, '901_1200' => 0.772, '1201_1800' => 0.808, '1801_6000' => 0.778),
    0.75 => array('600_900' => 0.750, '901_1200' => 0.789, '1201_1800' => 0.825, '1801_6000' => 0.807),
    1.1 => array('600_900' => 0.777, '901_1200' => 0.810, '1201_1800' => 0.841, '1801_6000' => 0.827),
    1.5 => array('600_900' => 0.797, '901_1200' => 0.825, '1201_1800' => 0.853, '1801_6000' => 0.842),
    2.2 => array('600_900' => 0.819, '901_1200' => 0.843, '1201_1800' => 0.867, '1801_6000' => 0.859),
    3 => array('600_900' => 0.835, '901_1200' => 0.856, '1201_1800' => 0.877, '1801_6000' => 0.871),
    4 => array('600_900' => 0.848, '901_1200' => 0.868, '1201_1800' => 0.886, '1801_6000' => 0.881),
    5.5 => array('600_900' => 0.862, '901_1200' => 0.880, '1201_1800' => 0.896, '1801_6000' => 0.892),
    7.5 => array('600_900' => 0.873, '901_1200' => 0.889, '1201_1800' => 0.904, '1801_6000' => 0.901),
    11 => array('600_900' => 0.886, '901_1200' => 0.903, '1201_1800' => 0.914, '1801_6000' => 0.912),
    15 => array('600_900' => 0.896, '901_1200' => 0.912, '1201_1800' => 0.921, '1801_6000' => 0.919),
    18.5 => array('600_900' => 0.901, '901_1200' => 0.917, '1201_1800' => 0.926, '1801_6000' => 0.924),
    22 => array('600_900' => 0.906, '901_1200' => 0.922, '1201_1800' => 0.930, '1801_6000' => 0.927),
    30 => array('600_900' => 0.913, '901_1200' => 0.929, '1201_1800' => 0.936, '1801_6000' => 0.933),
    37 => array('600_900' => 0.918, '901_1200' => 0.933, '1201_1800' => 0.939, '1801_6000' => 0.937),
    45 => array('600_900' => 0.922, '901_1200' => 0.937, '1201_1800' => 0.942, '1801_6000' => 0.940),
    55 => array('600_900' => 0.925, '901_1200' => 0.941, '1201_1800' => 0.946, '1801_6000' => 0.943),
    75 => array('600_900' => 0.931, '901_1200' => 0.946, '1201_1800' => 0.950, '1801_6000' => 0.947),
    90 => array('600_900' => 0.934, '901_1200' => 0.949, '1201_1800' => 0.952, '1801_6000' => 0.950),
    110 => array('600_900' => 0.937, '901_1200' => 0.951, '1201_1800' => 0.954, '1801_6000' => 0.952),
    132 => array('600_900' => 0.940, '901_1200' => 0.954, '1201_1800' => 0.956, '1801_6000' => 0.954),
    160 => array('600_900' => 0.943, '901_1200' => 0.956, '1201_1800' => 0.958, '1801_6000' => 0.956),
    200 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    250 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    315 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    355 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    400 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    450 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    500 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958),
    1000 => array('600_900' => 0.946, '901_1200' => 0.958, '1201_1800' => 0.960, '1801_6000' => 0.958)
),
        "IE4" => array(
    0.12 => array('600_900' => 0.623, '901_1200' => 0.649, '1201_1800' => 0.698, '1801_6000' => 0.665),
    0.18 => array('600_900' => 0.672, '901_1200' => 0.701, '1201_1800' => 0.747, '1801_6000' => 0.708),
    0.20 => array('600_900' => 0.684, '901_1200' => 0.714, '1201_1800' => 0.758, '1801_6000' => 0.719),
    0.25 => array('600_900' => 0.708, '901_1200' => 0.741, '1201_1800' => 0.779, '1801_6000' => 0.743),
    0.37 => array('600_900' => 0.748, '901_1200' => 0.780, '1201_1800' => 0.811, '1801_6000' => 0.781),
    0.40 => array('600_900' => 0.749, '901_1200' => 0.787, '1201_1800' => 0.817, '1801_6000' => 0.789),
    0.55 => array('600_900' => 0.770, '901_1200' => 0.809, '1201_1800' => 0.839, '1801_6000' => 0.815),
    0.75 => array('600_900' => 0.784, '901_1200' => 0.827, '1201_1800' => 0.857, '1801_6000' => 0.835),
    1.1 => array('600_900' => 0.808, '901_1200' => 0.845, '1201_1800' => 0.872, '1801_6000' => 0.852),
    1.5 => array('600_900' => 0.826, '901_1200' => 0.859, '1201_1800' => 0.882, '1801_6000' => 0.865),
    2.2 => array('600_900' => 0.845, '901_1200' => 0.874, '1201_1800' => 0.895, '1801_6000' => 0.880),
    3 => array('600_900' => 0.859, '901_1200' => 0.886, '1201_1800' => 0.904, '1801_6000' => 0.895),
    4 => array('600_900' => 0.871, '901_1200' => 0.895, '1201_1800' => 0.911, '1801_6000' => 0.900),
    5.5 => array('600_900' => 0.883, '901_1200' => 0.905, '1201_1800' => 0.919, '1801_6000' => 0.909),
    7.5 => array('600_900' => 0.893, '901_1200' => 0.913, '1201_1800' => 0.926, '1801_6000' => 0.917),
    11 => array('600_900' => 0.904, '901_1200' => 0.923, '1201_1800' => 0.933, '1801_6000' => 0.926),
    15 => array('600_900' => 0.912, '901_1200' => 0.929, '1201_1800' => 0.939, '1801_6000' => 0.933),
    18.5 => array('600_900' => 0.917, '901_1200' => 0.934, '1201_1800' => 0.942, '1801_6000' => 0.937),
    22 => array('600_900' => 0.921, '901_1200' => 0.937, '1201_1800' => 0.945, '1801_6000' => 0.940),
    30 => array('600_900' => 0.927, '901_1200' => 0.942, '1201_1800' => 0.949, '1801_6000' => 0.945),
    37 => array('600_900' => 0.931, '901_1200' => 0.945, '1201_1800' => 0.952, '1801_6000' => 0.948),
    45 => array('600_900' => 0.934, '901_1200' => 0.948, '1201_1800' => 0.954, '1801_6000' => 0.950),
    55 => array('600_900' => 0.937, '901_1200' => 0.951, '1201_1800' => 0.957, '1801_6000' => 0.953),
    75 => array('600_900' => 0.942, '901_1200' => 0.954, '1201_1800' => 0.960, '1801_6000' => 0.956),
    90 => array('600_900' => 0.944, '901_1200' => 0.956, '1201_1800' => 0.961, '1801_6000' => 0.958),
    110 => array('600_900' => 0.947, '901_1200' => 0.958, '1201_1800' => 0.963, '1801_6000' => 0.960),
    132 => array('600_900' => 0.949, '901_1200' => 0.960, '1201_1800' => 0.964, '1801_6000' => 0.962),
    160 => array('600_900' => 0.951, '901_1200' => 0.962, '1201_1800' => 0.966, '1801_6000' => 0.963),
    200 => array('600_900' => 0.954, '901_1200' => 0.963, '1201_1800' => 0.967, '1801_6000' => 0.965),
    250 => array('600_900' => 0.954, '901_1200' => 0.965, '1201_1800' => 0.967, '1801_6000' => 0.965),
    315 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965),
    355 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965),
    400 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965),
    450 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965),
    500 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965),
    1000 => array('600_900' => 0.954, '901_1200' => 0.966, '1201_1800' => 0.967, '1801_6000' => 0.965)
),
       "IE5" => array(
    0.12 => array('600_900' => 0.674, '901_1200' => 0.698, '1201_1800' => 0.743, '1801_6000' => 0.714),
    0.18 => array('600_900' => 0.719, '901_1200' => 0.746, '1201_1800' => 0.787, '1801_6000' => 0.752),
    0.20 => array('600_900' => 0.730, '901_1200' => 0.757, '1201_1800' => 0.796, '1801_6000' => 0.762),
    0.25 => array('600_900' => 0.752, '901_1200' => 0.781, '1201_1800' => 0.815, '1801_6000' => 0.783),
    0.37 => array('600_900' => 0.784, '901_1200' => 0.816, '1201_1800' => 0.843, '1801_6000' => 0.817),
    0.40 => array('600_900' => 0.789, '901_1200' => 0.822, '1201_1800' => 0.848, '1801_6000' => 0.823),
    0.55 => array('600_900' => 0.806, '901_1200' => 0.842, '1201_1800' => 0.867, '1801_6000' => 0.846),
    0.75 => array('600_900' => 0.820, '901_1200' => 0.857, '1201_1800' => 0.882, '1801_6000' => 0.863),
    1.1 => array('600_900' => 0.840, '901_1200' => 0.872, '1201_1800' => 0.895, '1801_6000' => 0.878),
    1.5 => array('600_900' => 0.855, '901_1200' => 0.884, '1201_1800' => 0.904, '1801_6000' => 0.889),
    2.2 => array('600_900' => 0.872, '901_1200' => 0.897, '1201_1800' => 0.914, '1801_6000' => 0.902),
    3 => array('600_900' => 0.884, '901_1200' => 0.906, '1201_1800' => 0.921, '1801_6000' => 0.911),
    4 => array('600_900' => 0.894, '901_1200' => 0.914, '1201_1800' => 0.928, '1801_6000' => 0.918),
    5.5 => array('600_900' => 0.904, '901_1200' => 0.922, '1201_1800' => 0.934, '1801_6000' => 0.926),
    7.5 => array('600_900' => 0.913, '901_1200' => 0.929, '1201_1800' => 0.940, '1801_6000' => 0.933),
    11 => array('600_900' => 0.922, '901_1200' => 0.937, '1201_1800' => 0.946, '1801_6000' => 0.940),
    15 => array('600_900' => 0.929, '901_1200' => 0.943, '1201_1800' => 0.951, '1801_6000' => 0.945),
    18.5 => array('600_900' => 0.933, '901_1200' => 0.946, '1201_1800' => 0.953, '1801_6000' => 0.948),
    22 => array('600_900' => 0.936, '901_1200' => 0.949, '1201_1800' => 0.955, '1801_6000' => 0.951),
    30 => array('600_900' => 0.941, '901_1200' => 0.953, '1201_1800' => 0.959, '1801_6000' => 0.955),
    37 => array('600_900' => 0.944, '901_1200' => 0.956, '1201_1800' => 0.961, '1801_6000' => 0.958),
    45 => array('600_900' => 0.947, '901_1200' => 0.958, '1201_1800' => 0.963, '1801_6000' => 0.960),
    55 => array('600_900' => 0.949, '901_1200' => 0.960, '1201_1800' => 0.965, '1801_6000' => 0.962),
    75 => array('600_900' => 0.953, '901_1200' => 0.963, '1201_1800' => 0.967, '1801_6000' => 0.965),
    90 => array('600_900' => 0.955, '901_1200' => 0.965, '1201_1800' => 0.969, '1801_6000' => 0.966),
    110 => array('600_900' => 0.957, '901_1200' => 0.966, '1201_1800' => 0.970, '1801_6000' => 0.968),
    132 => array('600_900' => 0.959, '901_1200' => 0.968, '1201_1800' => 0.971, '1801_6000' => 0.969),
    160 => array('600_900' => 0.961, '901_1200' => 0.969, '1201_1800' => 0.972, '1801_6000' => 0.970),
    200 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    250 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    315 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    355 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    400 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    450 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    500 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972),
    1000 => array('600_900' => 0.963, '901_1200' => 0.970, '1201_1800' => 0.973, '1801_6000' => 0.972)
)
    ),
    'coutMoteurs' => array(
        "IE2" => array(
            0.12 => 100, 0.18 => 120, 0.20 => 130, 0.25 => 150, 0.37 => 180, 0.4 => 190, 0.55 => 220, 0.75 => 250,
            1.1 => 300, 1.5 => 350, 2.2 => 450, 3 => 550, 4 => 650, 5.5 => 700, 7.5 => 900,
            11 => 1200, 15 => 1500, 18.5 => 1800, 22 => 2000, 30 => 2500, 37 => 3000, 45 => 3500,
            55 => 4500, 75 => 6000, 90 => 7000, 110 => 8500, 132 => 10000, 160 => 12000, 200 => 15000,
            250 => 18000, 315 => 22000, 355 => 25000, 400 => 28000, 450 => 32000, 500 => 35000, 1000 => 70000
        ),
        "IE3" => array(
            0.12 => 120, 0.18 => 140, 0.20 => 150, 0.25 => 180, 0.37 => 220, 0.4 => 230, 0.55 => 260, 0.75 => 300,
            1.1 => 350, 1.5 => 420, 2.2 => 550, 3 => 650, 4 => 800, 5.5 => 850, 7.5 => 1100,
            11 => 1500, 15 => 1800, 18.5 => 2200, 22 => 2500, 30 => 3200, 37 => 3800, 45 => 4200,
            55 => 5500, 75 => 7500, 90 => 8500, 110 => 10000, 132 => 12000, 160 => 15000, 200 => 18000,
            250 => 22000, 315 => 27000, 355 => 30000, 400 => 34000, 450 => 38000, 500 => 42000, 1000 => 85000
        ),
        "IE4" => array(
            0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 230, 0.37 => 280, 0.4 => 300, 0.55 => 350, 0.75 => 420,
            1.1 => 500, 1.5 => 600, 2.2 => 750, 3 => 900, 4 => 1100, 5.5 => 1200, 7.5 => 1500,
            11 => 2000, 15 => 2500, 18.5 => 3000, 22 => 3500, 30 => 4500, 37 => 5200, 45 => 6000,
            55 => 7500, 75 => 10000, 90 => 12000, 110 => 14000, 132 => 16000, 160 => 20000, 200 => 24000,
            250 => 30000, 315 => 36000, 355 => 40000, 400 => 45000, 450 => 50000, 500 => 55000, 1000 => 110000
        ),
        "IE5" => array(
            0.12 => 180, 0.18 => 220, 0.20 => 240, 0.25 => 280, 0.37 => 340, 0.4 => 360, 0.55 => 420, 0.75 => 500,
            1.1 => 600, 1.5 => 720, 2.2 => 900, 3 => 1100, 4 => 1300, 5.5 => 1500, 7.5 => 1800,
            11 => 2400, 15 => 3000, 18.5 => 3600, 22 => 4200, 30 => 5400, 37 => 6200, 45 => 7200,
            55 => 9000, 75 => 12000, 90 => 14000, 110 => 17000, 132 => 20000, 160 => 24000, 200 => 30000,
            250 => 36000, 315 => 44000, 355 => 48000, 400 => 54000, 450 => 60000, 500 => 66000, 1000 => 130000
        )
    ),
    'coutVSD' => array(
        0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 220, 0.37 => 250, 0.4 => 260, 0.55 => 300, 0.75 => 350,
        1.1 => 500, 1.5 => 550, 2.2 => 650, 3 => 750, 4 => 850, 5.5 => 900, 7.5 => 1200,
        11 => 1500, 15 => 1800, 18.5 => 2200, 22 => 2500, 30 => 3000, 37 => 3500, 45 => 4000,
        55 => 5000, 75 => 8000, 90 => 9500, 110 => 11000, 132 => 13000, 160 => 16000, 200 => 20000,
        250 => 25000, 315 => 30000, 355 => 34000, 400 => 38000, 450 => 42000, 500 => 45000, 1000 => 90000
    )
);



// Inclusion de Chart.js
wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js', array('jquery'), '3.7.1', true);
wp_enqueue_script('chart-js-annotation', 'https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/2.1.0/chartjs-plugin-annotation.min.js', array('chart-js'), '2.1.0', true);

// Définir un identifiant unique pour le simulateur
$simulateurId = 'simulateur_' . uniqid();

function impactIndicator($pourcentage) {
    if ($pourcentage >= 80) {
        $couleur = '#0D8538';
        $texte = 'Économies élevées';
    } elseif ($pourcentage >= 50) {
        $couleur = '#f59e0b';
        $texte = 'Économies moyennes';
    } else {
        $couleur = '#9ca3af';
        $texte = 'Économies faibles';
    }
    
    return '<span style="display:inline-flex; align-items:center; margin-left:0.1rem;">
        <span style="display:inline-block; width:16px; height:16px; border-radius:50%; background-color:' . $couleur . ';"></span>
        <span style="margin-left:5px; color:#000; font-size: 12px; font-weight: bold">' . $texte . '</span>
    </span>';
}
?>


<style>
    .simulateur-economie-energie {
        margin: 2rem auto;
        max-width: 1200px;
        color: #333;
    }
    
    .simulateur-card {
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .simulateur-header {
        background-color: #2563eb;
        color: white;
        padding: 1.5rem;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    
    .simulateur-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: #fff;
    }
    
    .simulateur-content {
        padding: 1.5rem;
        border: 1px solid #e5e7eb !important;
    }
    
    .simulateur-grid {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: repeat(2, 1fr);
    }
    

    .simulateur-full-width {
    grid-column: 1 / -1;
}


    .simulateur-input:focus,
    .simulateur-select:focus {
    outline: none;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 1px #2563eb !important;
    color: #000 !important;
    width: calc(100% + 10px);
    margin-left: -5px !important;
    margin-right: -5px !important;
    }

    .analyse-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
    vertical-align: middle;
    }

.analyse-text {
    vertical-align: middle;
   }

.simulateur-disclaimer {
    margin-top: 1rem;
    padding: 1rem;
    font-size: 0.875rem;
    color: #000 !important;
    text-align: center;
    border-top: 1px solid #000 !important;
   }

/* Grille pour les conditions d'exploitation */
.simulateur-conditions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

/* Grille pour les résultats détaillés */
.simulateur-results-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

.simulateur-analysis-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

.simulateur-results-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.simulateur-results-header {
    margin-bottom: 1rem;
}

.simulateur-results-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    /* Structure principale avec réduction dimensionnelle complète */
    .simulateur-economie-energie {
        margin: 0.5rem auto;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        padding: 0;
    }
    
    .simulateur-card {
        border-radius: 0.25rem;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 100%;
        margin: 0;
    }
    
    .simulateur-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .simulateur-section {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Forcer la largeur maximale sur tous les conteneurs */
    .simulateur-section,
    .simulateur-results-summary,
    .simulateur-savings,
    .simulateur-environmental,
    .simulateur-chart-container,
    .simulateur-input,
    .simulateur-select,
    .simulateur-input-group,
    .simulateur-inputs {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Éviter tout débordement de texte */
    .simulateur-economie-energie * {
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }
    
    /* Ajustement de la grille de puissance */
    .simulateur-puissance-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
        width: 100%;
        margin-top: 5px;
        padding: 0;
    }
    
    .simulateur-puissance-btn {
        font-size: 0.8rem;
        padding: 6px 2px;
        min-width: auto;
    }
    
    /* Correction des colonnes partout */
    .simulateur-conditions-grid,
    .simulateur-results-columns,
    .simulateur-analysis-grid,
    .simulateur-savings-grid,
    .simulateur-environmental-grid,
    .simulateur-results-grid {
        grid-template-columns: 1fr;
        gap: 0.8rem;
        width: 100%;
    }
    
    /* Réduire les marges et padding partout */
    .simulateur-input-group {
        margin-bottom: 0.5rem;
    }
    
    .simulateur-section h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .simulateur-results-summary {
        flex-direction: column !important;
        padding: 0.75rem;
        gap: 0.5rem;
    }
    
    /* Assurer que les graphiques s'adaptent */
    .simulateur-chart-container,
    .simulateur-chart-fullwidth {
        height: auto;
        min-height: 200px;
        width: 100%;
    }
    
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    
    /* Forcer les tableaux à s'adapter */
    table {
        width: 100%;
        display: block;
        overflow-x: auto;
        box-sizing: border-box;
    }
    
    /* Réduire la taille de la police partout */
    .simulateur-environmental-label,
    .simulateur-environmental-value {
        font-size: 0.8rem !important;
    }

    /* Correction header */
    .simulateur-header {
        padding: 1rem;
    }
    
    .simulateur-header h2 {
        font-size: 1.25rem;
    }

    /* Styles pour assurer que le contenu reste confiné */
    .simulateur-section-special,
    .simulateur-section-last {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        margin-left: 0;
        margin-right: 0;
    }

    .simulateur-section
     {
        max-width: 64% !important;
    }

    .simulateur-savings,
    .simulateur-environmental {
        max-width: 62% !important;
        margin: 0 !important;
    }

    .simulateur-analysis {
        max-width: 62% !important;
        margin: 0 !important;
        margin-top: 1rem !important;
    }

    .simulateur-input-header {
        flex-direction: column;
    }
.simulateur-input-group:first-child label,
.simulateur-input-group:first-of-type label {
    margin-bottom: 0;
}

.simulateur-grid,
.simulateur-result-row,
.simulateur-results-summary {
gap: 0rem !important;
}
.simulateur-result-value {
    margin-bottom: 0rem !important;
}
.simulateur-content {
    padding: 1.3rem !important;
}
.simulateur-section-special {
    margin-top: 0rem !important;
}
.simulateur-input-group label {
    flex-wrap: wrap;
}
.switch-wrap {
    flex-wrap: wrap;
}
}

@media (min-width: 992px) {
    .simulateur-results-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .simulateur-analysis-grid {
        grid-template-columns: 1fr;
    }
    .simulateur-results-columns {
        grid-template-columns: 1fr;
    }
}
    
    .simulateur-section {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .simulateur-section-special {
    margin-top: -1.5rem;
}

.simulateur-section-last {
    margin-bottom: 0;
}
    
    .simulateur-section h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-size: 1.25rem;
        font-weight: bold;
        color: #000;
    }
    
    .simulateur-inputs {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .simulateur-chart-fullwidth {
    grid-column: 1 / -1;
    margin-bottom: 1.5rem;
    height: 20rem;
    position: relative;
}
    
    .simulateur-input-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .simulateur-input-header {
        display: flex;
        justify-content: space-between;
    }
    
    .simulateur-value {
        font-size: 0.875rem;
        font-weight: bold;
    }
    
    .simulateur-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #e5e7eb;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: background-color 0.2s;
    }
    
    .simulateur-button:hover {
        background-color: #d1d5db;
    }
    
    .simulateur-accordion {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
        border: 1px solid #e5e7eb;
        border-radius: 0.25rem;
    }
    
    .simulateur-accordion-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.25rem;
        padding: 0.5rem;
    }
    
    .simulateur-power-button {
        font-size: 0.75rem;
        padding: 0.25rem;
        border: none;
        border-radius: 0.25rem;
        background-color: #1e40af;
        color: #FFF;
        cursor: pointer;
        transition: background-color 0.2s;
        font-weight: bold;
    }
    
    .simulateur-power-button:hover {
        background-color: #1e3a8a;
    }
    
    .simulateur-power-button.active {
        background-color: #38bdf8;
        color: white;
    }
    
    .simulateur-select {
        padding: 0.5rem;
        border: 1px solid #000 !important;
        border-radius: 0.25rem;
        background-color: #FFF;
        width: 100%;
    }
    
    .simulateur-input {
        padding: 0.5rem 1rem;
        border: 1px solid #000 !important;
        border-radius: 0.25rem;
        width: 100%;
    }
    
    .switch-group {
        align-items: center;
    }
    
    .switch-container {
        position: relative;
        display: inline-block;
        width: 3.5rem;
        height: 1.75rem;
    }
    
    .switch-input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .switch-label {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        border-radius: 1.75rem;
        cursor: pointer;
        transition: 0.3s;
    }
    
    .switch-label:before {
        position: absolute;
        content: "";
        height: 1.25rem;
        width: 1.25rem;
        left: 0.25rem;
        bottom: 0.25rem;
        background-color: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    
    .switch-input:checked + .switch-label {
        background-color: #2563eb;
    }
    
    .switch-input:checked + .switch-label:before {
        transform: translateX(1.75rem);
    }
    
    .simulateur-results {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .simulateur-results-summary {
        background-color: #fff;
        border: 1px solid #000;
        border-radius: 0.5rem;
        padding: 1rem;
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
        text-align: center;
        max-width: 1060px;
        margin: 0 auto;
        flex-wrap: wrap;
    }
    
    .simulateur-result-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .simulateur-result-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .simulateur-result-value {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
    }
    
    .simulateur-result-value.positive {
        color: #0D8538;
    }
    
    .simulateur-chart-container {
        height: 16rem;
        margin-bottom: 1rem;
    }
    
    .simulateur-chart-container h4 {
        font-size: 0.875rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .simulateur-analysis,
    .simulateur-savings,
    .simulateur-environmental {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .simulateur-analysis {
        background-color: #e0f2fe;
    }
    
    .simulateur-analysis h4,
    .simulateur-savings h4,
    .simulateur-environmental h4 {
        font-size: 0.875rem;
        font-weight: bold;
        margin-top: 0;
        margin-bottom: 0.5rem;
    }
    
    .simulateur-analysis p {
        font-size: 0.875rem;
        margin: 0;
    }
    
    .simulateur-savings {
        background-color: #ecfdf5;
        border: 1px solid #000;
        display: flex;
        flex-direction: column;
        text-align: center;
        width: 30rem;
        max-width: 690px;
        margin: 0 auto;
    }
    
    .simulateur-savings-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .simulateur-savings-label {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .simulateur-savings-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0D8538;
    }
    
    .simulateur-environmental {
        background-color: #fff;
        border: 1px solid #000;
        display: flex;
        flex-direction: column;
        text-align: center;
        width: 30rem;
        max-width: 690px;
        margin: 0 auto;
    }
    
    .simulateur-environmental-grid {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .simulateur-environmental-row {
        display: flex;
        justify-content: space-between;
    }
    
    .simulateur-environmental-label {
        font-size: 0.875rem;
        color: #6b7280;
        display: flex;
    align-items: center;
    gap: 0.3rem;
    }

    .simulateur-environmental-label-2 {
        display: flex;
        justify-content: center;
    align-items: center;
    gap: 0.3rem;
    }

 

.simulateur-savings-2 {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    
    .simulateur-environmental-value {
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .simulateur-disclaimer {
        margin-top: 1rem;
        padding: 1rem;
        font-size: 0.875rem;
        color: #000 !important;
        text-align: center;
    }
    /* Ajoutez ceci à votre section CSS existante */
.simulateur-puissance-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 10px;
    padding-left: 4px;
    padding-right: 4px;
}

.simulateur-puissance-btn {
    background-color: #1e40af;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 0;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s;
    text-align: center;
    font-weight: bold;
}

.simulateur-puissance-btn:hover {
    background-color: #1e3a8a;
}

.simulateur-puissance-btn.selected {
    background-color: #38bdf8;
    font-weight: bold;
    color: #000;
}

.text-bold {
    font-weight: bold;
}

.text-bold-black {
    font-weight: bold;
    color: #000;
}
.text-black {
    color: #000;
}

.simulateur-input-header label svg,
.simulateur-input-group label svg {
    vertical-align: middle;
    margin-right: 0.3rem ;
}

.simulateur-input-group label {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    margin-left: 0.1rem;
}

.simulateur-input-group label svg.variateur-icon {
    margin-left: 0.5rem;
    margin-right: 0;
}

.simulateur-input-group.switch-group label {
    margin-bottom: 0;
}

.simulateur-label {
    display: flex;
    align-items: center;
    margin-bottom: 0;
    margin-right: 0.3rem;
    gap: 0.3rem;
}

.help-icon {
      width: 24px;
      padding: 0;
      background: none;
      border: none;
      cursor: pointer;
      color: #555555; /* Noir léger par défaut */
      transition: color 0.2s, transform 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 0.3rem !important;
    }

.switch-wrap {
    display: flex; align-items: center; width: 100%
}
    
    .help-icon:not(.active):hover {
      color: #2563eb;
      transform: scale(1.15);
    }
    
    
    .help-icon.active {
      color: #2563eb;
      transform: scale(1.15);
    }
    
    
    .help-icon.active:hover {
      color: #2563eb;
    }
    
    .help-icon svg {
      width: 100%;
      height: 100%;
      margin-right: 0.3rem !important;
    }
    
    .help-content {
      overflow: hidden;
      max-height: 0;
      opacity: 0;
      margin: 0;
      position: absolute;
      pointer-events: none;
    }
    
    .help-content.visible {
      max-height: 500px;
      opacity: 1;
      margin: 15px 0;
      position: static;
      pointer-events: auto;
    }
    
    .info-box {
      background-color: #f9fafb;
      border-left: 4px solid #2563eb;
      border-radius: 0 4px 4px 0;
      padding: 12px 15px;
      color: #000;
    }
    
    .info-box p {
      margin-top: 0;
      font-size: 14px;
      line-height: 1.5;
    }
    
    .info-box p:last-child {
      margin-bottom: 0;
    }

    .form-field {
  position: relative;
  margin-bottom: 15px;
}

.form-field svg {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
}

.form-field input,
.form-field select {
  padding-left: 40px; /* Espace pour l'icône */
}

.screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

</style>

<div class="simulateur-economie-energie">
    <div class="simulateur-card">
        <div class="simulateur-header">
            <h2>Simulateur d'Économies d'Énergie pour Moteurs</h2>
        </div>
        
        <div class="simulateur-content">
            <div class="simulateur-grid">
                <!-- COLONNE 1 - Paramètres d'entrée -->
                    <!-- Moteur actuel -->
                    <div class="simulateur-section">
                        <h3 class="text-bold-black">Moteur actuel :</h3>
                        
                        <div class="simulateur-inputs">
                            <!-- Puissance accordéon -->
                            <div class="simulateur-input-group">
                                <div class="simulateur-input-header">
                                    <label for="puissanceActuelle_<?php echo $simulateurId; ?>" class="text-bold-black">
                                    <svg fill="#000000" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 491.52 491.52" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M471.04,163.84h-32.768c-8.192,0-16.384,6.144-20.48,14.336l-18.432,81.92l-14.336-40.96 c0-8.192-8.192-14.336-16.384-14.336h-40.96v-20.48c0-12.288-8.192-20.48-20.48-20.48H81.92c-10.24,0-20.48,8.192-20.48,20.48 v163.84c0,2.048,0,6.144,2.048,8.192l28.672,61.44c4.096,8.192,10.24,12.288,18.432,12.288H358.4 c8.192,0,14.336-4.096,18.432-10.24l18.432-30.72h6.144l18.432,49.152c4.096,8.192,10.24,12.288,18.432,12.288h32.768 c12.288,0,20.48-8.192,20.48-20.48V184.32C491.52,172.032,483.328,163.84,471.04,163.84z M458.752,421.888l-24.576-61.44 c-4.096-6.144-10.24-12.288-20.48-12.288h-32.768c-8.192,0-14.336,4.096-18.432,10.24l-16.384,30.72H124.928L102.4,344.064V204.8 h184.32v20.48c0,12.288,8.192,20.48,20.48,20.48h47.104l8.192,26.624c2.048,8.192,10.24,14.336,18.432,14.336h32.768 c8.192,0,16.384-6.144,20.48-14.336l24.576-81.92V421.888z"></path> </g> </g> <g> <g> <path d="M81.92,266.24H20.48C8.192,266.24,0,274.432,0,286.72c0,12.288,10.24,20.48,20.48,20.48h61.44 c12.288,0,20.48-8.192,20.48-20.48C102.4,274.432,94.208,266.24,81.92,266.24z"></path> </g> </g> <g> <g> <path d="M20.48,225.28C8.192,225.28,0,233.472,0,245.76v81.92c0,12.288,8.192,20.48,20.48,20.48c12.288,0,20.48-8.192,20.48-20.48 v-81.92C40.96,233.472,32.768,225.28,20.48,225.28z"></path> </g> </g> <g> <g> <path d="M245.76,102.4h-81.92c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h81.92 c12.288,0,20.48-8.192,20.48-20.48v-61.44C266.24,110.592,258.048,102.4,245.76,102.4z M225.28,163.84h-40.96v-20.48h40.96V163.84 z"></path> </g> </g> <g> <g> <path d="M286.72,40.96H122.88c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h163.84 c12.288,0,20.48-8.192,20.48-20.48V61.44C307.2,49.152,299.008,40.96,286.72,40.96z M266.24,102.4H143.36V81.92h122.88V102.4z"></path> </g> </g> </g></svg>
                                        Puissance du moteur actuel (kW) <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(60); ?></label>
                                        
                                    <span class="simulateur-value text-bold-black" id="puissanceActuelleValue_<?php echo $simulateurId; ?>">11 kW</span>
                                </div>

                                <div class="help-content">
                                    <div class="info-box">
                                        <p>Dimensionner correctement la puissance du moteur permet d'économiser entre 5% et 15% sur les coûts d'électricité annuels.</p>
                                    </div>
                                </div>

                                <div class="simulateur-category-selector">
    <label for="puissanceCategoryActuelle_<?php echo $simulateurId; ?>" class="screen-reader-text">Catégorie de puissance du moteur actuel</label>
    <select id="puissanceCategoryActuelle_<?php echo $simulateurId; ?>" class="simulateur-select" aria-describedby="puissanceCategoryDesc_<?php echo $simulateurId; ?>">
        <option value="micro">Micro-moteurs (0.12 kW - 0.75 kW)</option>
        <option value="petit" selected>Petits moteurs (1.1 kW - 11 kW)</option>
        <option value="moyen">Moteurs moyens (15 kW - 75 kW)</option>
        <option value="grand">Grands moteurs (90 kW - 1000 kW)</option>
    </select>
    <div id="puissanceCategoryDesc_<?php echo $simulateurId; ?>" class="screen-reader-text">Sélectionnez une catégorie pour afficher les puissances disponibles</div>
    <div class="simulateur-puissance-grid" id="puissanceActuelleGrid_<?php echo $simulateurId; ?>" role="radiogroup" aria-label="Options de puissance du moteur">
        <!-- Rempli dynamiquement par JavaScript -->
    </div>
</div>
                            </div>

                            
                            <!-- Nombre de pôles -->
                            <div class="simulateur-input-group">
                                <label for="polesActuel_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cog-icon lucide-cog"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="M12 2v2"/><path d="M12 22v-2"/><path d="m17 20.66-1-1.73"/><path d="M11 10.27 7 3.34"/><path d="m20.66 17-1.73-1"/><path d="m3.34 7 1.73 1"/><path d="M14 12h8"/><path d="M2 12h2"/><path d="m20.66 7-1.73 1"/><path d="m3.34 17 1.73-1"/><path d="m17 3.34-1 1.73"/><path d="m11 13.73-4 6.93"/></svg>
                                    Nombre de pôles (vitesse)  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(30); ?></label>

                                    <div class="help-content">
                                    <div class="info-box">
                                        <p>Détermine la vitesse de rotation du moteur : plus le nombre de pôles est élevé, plus la vitesse est faible. Les moteurs à vitesse réduite (6-8 pôles) permettent de faire des économies d'argent et d'énergie, durent plus longtemps, sont moins bruyants et nécessitent moins d'entretien mais coûtent plus cher à l'achat.</p>
                                        <p>Un 4 pôles (1500 tr/min) offre souvent le meilleur compromis entre performance et rapport qualité-prix et convient à la majorité des applications industrielles.</p>
                                    </div>
                                </div>

                                <select id="polesActuel_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="2">2 pôles (3000 tr/min)</option>
                                    <option value="4" selected>4 pôles (1500 tr/min)</option>
                                    <option value="6">6 pôles (1000 tr/min)</option>
                                    <option value="8">8 pôles (750 tr/min)</option>
                                </select>
                            </div>
                            
                            <!-- Classe d'efficience -->
                            <div class="simulateur-input-group">
                                <label for="classeActuelle_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg width="24px" height="24px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="si-glyph si-glyph-chart-column" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>872</title> <defs> </defs> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(0.000000, 1.000000)" fill="#434343"> <path d="M16,13.031 L0.984,13.031 L0.984,0.016 L0.027,0.016 L0,13.95 L0.027,13.95 L0.027,13.979 L16,13.95 L16,13.031 Z" class="si-glyph-fill"> </path> <path d="M4.958,7.021 L2.016,7.021 L2.016,11.985 L4.958,11.985 L4.958,7.021 L4.958,7.021 Z" class="si-glyph-fill"> </path> <path d="M9.969,5.047 L7.016,5.047 L7.016,11.969 L9.969,11.969 L9.969,5.047 L9.969,5.047 Z" class="si-glyph-fill"> </path> <path d="M14.953,3.031 L12,3.031 L12,11.978 L14.953,11.978 L14.953,3.031 L14.953,3.031 Z" class="si-glyph-fill"> </path> </g> </g> </g></svg>
                                    Classe d'efficience  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(85); ?></label>

                                    <div class="help-content">
                                    <div class="info-box">
                                     <p>Chaque niveau d'amélioration de classe d'efficience (par exemple, passer d'IE2 à IE3) génère des économies d'argent et d'énergie annuelles de 2% à 4%.</p>
                                    </div>
                                    </div>
                                <select id="classeActuelle_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="IE1">IE1 (Standard)</option>
                                    <option value="IE2" selected>IE2 (Haut rendement)</option>
                                    <option value="IE3">IE3 (Premium)</option>
                                    <option value="IE4">IE4 (Super Premium)</option>
                                    <option value="IE5">IE5 (Ultra Premium)</option>
                                </select>
                            </div>
                            <div class="simulateur-input-group">
                                <label for="efficaciteMoteurActuel_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg fill="#000000" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 491.52 491.52" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M471.04,163.84h-32.768c-8.192,0-16.384,6.144-20.48,14.336l-18.432,81.92l-14.336-40.96 c0-8.192-8.192-14.336-16.384-14.336h-40.96v-20.48c0-12.288-8.192-20.48-20.48-20.48H81.92c-10.24,0-20.48,8.192-20.48,20.48 v163.84c0,2.048,0,6.144,2.048,8.192l28.672,61.44c4.096,8.192,10.24,12.288,18.432,12.288H358.4 c8.192,0,14.336-4.096,18.432-10.24l18.432-30.72h6.144l18.432,49.152c4.096,8.192,10.24,12.288,18.432,12.288h32.768 c12.288,0,20.48-8.192,20.48-20.48V184.32C491.52,172.032,483.328,163.84,471.04,163.84z M458.752,421.888l-24.576-61.44 c-4.096-6.144-10.24-12.288-20.48-12.288h-32.768c-8.192,0-14.336,4.096-18.432,10.24l-16.384,30.72H124.928L102.4,344.064V204.8 h184.32v20.48c0,12.288,8.192,20.48,20.48,20.48h47.104l8.192,26.624c2.048,8.192,10.24,14.336,18.432,14.336h32.768 c8.192,0,16.384-6.144,20.48-14.336l24.576-81.92V421.888z"></path> </g> </g> <g> <g> <path d="M81.92,266.24H20.48C8.192,266.24,0,274.432,0,286.72c0,12.288,10.24,20.48,20.48,20.48h61.44 c12.288,0,20.48-8.192,20.48-20.48C102.4,274.432,94.208,266.24,81.92,266.24z"></path> </g> </g> <g> <g> <path d="M20.48,225.28C8.192,225.28,0,233.472,0,245.76v81.92c0,12.288,8.192,20.48,20.48,20.48c12.288,0,20.48-8.192,20.48-20.48 v-81.92C40.96,233.472,32.768,225.28,20.48,225.28z"></path> </g> </g> <g> <g> <path d="M245.76,102.4h-81.92c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h81.92 c12.288,0,20.48-8.192,20.48-20.48v-61.44C266.24,110.592,258.048,102.4,245.76,102.4z M225.28,163.84h-40.96v-20.48h40.96V163.84 z"></path> </g> </g> <g> <g> <path d="M286.72,40.96H122.88c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h163.84 c12.288,0,20.48-8.192,20.48-20.48V61.44C307.2,49.152,299.008,40.96,286.72,40.96z M266.24,102.4H143.36V81.92h122.88V102.4z"></path> </g> </g> </g></svg>
                                    Efficacité du moteur (%)  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button>  <?php echo impactIndicator(85); ?></label>
                                    <div class="help-content">
  <div class="info-box">
  <p>Une augmentation de 1% d'efficacité moteur réduit génère des économies d'argent er d'énergie annuelles de 1% à 1,2%. Les moteurs industriels modernes présentent généralement une efficacité entre 80% et 97%.</p>
  <p>Cette valeur est automatiquement déterminée selon :</p>
    <ul>
      <li>La puissance de votre moteur (kW)</li>
      <li>Sa classe d'efficience énergétique (IE1 à IE5)</li>
      <li>Sa vitesse de rotation (nombre de pôles)</li>
    </ul>
  </div>
</div>
                                <input
                                    id="efficaciteMoteurActuel_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="10"
                                    max="100"
                                    value="89"
                                    class="simulateur-input"
                                    readonly="readonly"
                                    style="background-color: #fff; cursor: default; opacity: 0.4;"
                                />
                            </div>
                            <!-- Variateur de vitesse -->
                            <div class="simulateur-input-group switch-group">
                            <div class="switch-wrap">
                            <span class="text-bold-black simulateur-label">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12M22 12C22 6.47715 17.5228 2 12 2M22 12H19.5M2 12C2 6.47715 6.47715 2 12 2M2 12H4.5M12 2V4.5M19.0784 5L13.4999 10.5M19.0784 19.0784L18.8745 18.8745C18.1827 18.1827 17.8368 17.8368 17.4331 17.5894C17.0753 17.3701 16.6851 17.2085 16.2769 17.1105C15.8166 17 15.3274 17 14.349 17L9.65096 17C8.6726 17 8.18342 17 7.72307 17.1106C7.31493 17.2086 6.92475 17.3702 6.56686 17.5895C6.1632 17.8369 5.8173 18.1828 5.12549 18.8746L4.92163 19.0784M4.92163 5L6.65808 6.73645M14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                    Variateur de vitesse</span>
                                <div class="switch-container">
                                    <input type="checkbox" id="vitesseVariableActuel_<?php echo $simulateurId; ?>" class="switch-input">
                                    <label for="vitesseVariableActuel_<?php echo $simulateurId; ?>" class="switch-label" aria-label="Vitesse variable"></label>
                                </div>
                                <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(85); ?>
                            </div>
                            <div class="help-content">
  <div class="info-box">
    <p>Permet d'ajuster la vitesse du moteur en fonction des besoins, évitant le fonctionnement constant à pleine puissance.</p>
    <p>Représente l'investissement le plus rentable pour les applications à charge variable comme les pompes générant des d'économies d'argent et d'énergies de 10% à 20% avec un retour sur investissement en 1 à 3 ans.</p>
    <p>À éviter: Pour les applications à charge constante fonctionnant toujours à pleine puissance, un variateur peut réduire légèrement l'efficacité globale (1% à 3%).</p>
    <p><strong>Pour moteurs ≤ 90 kW : Coefficient de réduction = 0,85 (économie de 15%)</strong></p>
    <p><strong>Pour moteurs > 90 kW : Coefficient de réduction = 0,75 (économie de 25%)</strong></p>
  </div>
</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Moteur cible -->
                    <div class="simulateur-section">
                        <h3>Moteur cible :</h3>
                        
                        <div class="simulateur-inputs">
                            <!-- Puissance sélecteur cible -->
                            <div class="simulateur-input-group">
                                <div class="simulateur-input-header">
                                    <label for="puissanceCible_<?php echo $simulateurId; ?>" class="text-bold-black">
                                    <svg fill="#000000" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 491.52 491.52" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M471.04,163.84h-32.768c-8.192,0-16.384,6.144-20.48,14.336l-18.432,81.92l-14.336-40.96 c0-8.192-8.192-14.336-16.384-14.336h-40.96v-20.48c0-12.288-8.192-20.48-20.48-20.48H81.92c-10.24,0-20.48,8.192-20.48,20.48 v163.84c0,2.048,0,6.144,2.048,8.192l28.672,61.44c4.096,8.192,10.24,12.288,18.432,12.288H358.4 c8.192,0,14.336-4.096,18.432-10.24l18.432-30.72h6.144l18.432,49.152c4.096,8.192,10.24,12.288,18.432,12.288h32.768 c12.288,0,20.48-8.192,20.48-20.48V184.32C491.52,172.032,483.328,163.84,471.04,163.84z M458.752,421.888l-24.576-61.44 c-4.096-6.144-10.24-12.288-20.48-12.288h-32.768c-8.192,0-14.336,4.096-18.432,10.24l-16.384,30.72H124.928L102.4,344.064V204.8 h184.32v20.48c0,12.288,8.192,20.48,20.48,20.48h47.104l8.192,26.624c2.048,8.192,10.24,14.336,18.432,14.336h32.768 c8.192,0,16.384-6.144,20.48-14.336l24.576-81.92V421.888z"></path> </g> </g> <g> <g> <path d="M81.92,266.24H20.48C8.192,266.24,0,274.432,0,286.72c0,12.288,10.24,20.48,20.48,20.48h61.44 c12.288,0,20.48-8.192,20.48-20.48C102.4,274.432,94.208,266.24,81.92,266.24z"></path> </g> </g> <g> <g> <path d="M20.48,225.28C8.192,225.28,0,233.472,0,245.76v81.92c0,12.288,8.192,20.48,20.48,20.48c12.288,0,20.48-8.192,20.48-20.48 v-81.92C40.96,233.472,32.768,225.28,20.48,225.28z"></path> </g> </g> <g> <g> <path d="M245.76,102.4h-81.92c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h81.92 c12.288,0,20.48-8.192,20.48-20.48v-61.44C266.24,110.592,258.048,102.4,245.76,102.4z M225.28,163.84h-40.96v-20.48h40.96V163.84 z"></path> </g> </g> <g> <g> <path d="M286.72,40.96H122.88c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h163.84 c12.288,0,20.48-8.192,20.48-20.48V61.44C307.2,49.152,299.008,40.96,286.72,40.96z M266.24,102.4H143.36V81.92h122.88V102.4z"></path> </g> </g> </g></svg>
                                        Puissance du moteur cible (kW)  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(60); ?></label>
                                    <span class="simulateur-value text-bold-black" id="puissanceCibleValue_<?php echo $simulateurId; ?>">11 kW</span>
                                </div>
                                <div class="help-content">
                                    <div class="info-box">
                                        <p>Dimensionner correctement la puissance du moteur permet d'économiser entre 5% et 15% sur les coûts d'électricité annuels.</p>
                                    </div>
                                </div>
                                <div class="simulateur-category-selector">
    <label for="puissanceCategoryCible_<?php echo $simulateurId; ?>" class="screen-reader-text">Catégorie de puissance du moteur cible</label>
    <select id="puissanceCategoryCible_<?php echo $simulateurId; ?>" class="simulateur-select" aria-describedby="puissanceCategoryDesc_<?php echo $simulateurId; ?>">
        <option value="micro">Micro-moteurs (0.12 kW - 0.75 kW)</option>
        <option value="petit" selected>Petits moteurs (1.1 kW - 11 kW)</option>
        <option value="moyen">Moteurs moyens (15 kW - 75 kW)</option>
        <option value="grand">Grands moteurs (90 kW - 1000 kW)</option>
    </select>
    <div id="puissanceCategoryDesc_<?php echo $simulateurId; ?>" class="screen-reader-text">Sélectionnez une catégorie pour afficher les puissances disponibles</div>
    <div class="simulateur-puissance-grid" id="puissanceCibleGrid_<?php echo $simulateurId; ?>" role="radiogroup" aria-label="Options de puissance du moteur">
        <!-- Rempli dynamiquement par JavaScript -->
    </div>
</div>
                            </div>
                            
                            <!-- Nombre de pôles cible -->
                            <div class="simulateur-input-group">
                                <label for="polesCible_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/>
        <path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
        <path d="M12 2v2"/>
        <path d="M12 22v-2"/>
        <path d="m17 20.66-1-1.73"/>
        <path d="M11 10.27 7 3.34"/>
        <path d="m20.66 17-1.73-1"/>
        <path d="m3.34 7 1.73 1"/>
        <path d="M14 12h8"/>
        <path d="M2 12h2"/>
        <path d="m20.66 7-1.73 1"/>
        <path d="m3.34 17 1.73-1"/>
        <path d="m17 3.34-1 1.73"/>
        <path d="m11 13.73-4 6.93"/>
    </svg>
                                    Nombre de pôles (vitesse)  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button> <?php echo impactIndicator(30); ?></label>
                                    <div class="help-content">
  <div class="info-box">
    <p>Détermine la vitesse de rotation du moteur : plus le nombre de pôles est élevé, plus la vitesse est faible. Les moteurs à vitesse réduite (6-8 pôles) permettent de faire des économies d'argent et d'énergie, durent plus longtemps, sont moins bruyants et nécessitent moins d'entretien mais coûtent plus cher à l'achat.</p>
    <p>Un 4 pôles (1500 tr/min) offre souvent le meilleur compromis entre performance et rapport qualité-prix et convient à la majorité des applications industrielles.</p>
  </div>
</div>
                                <select id="polesCible_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="2">2 pôles (3000 tr/min)</option>
                                    <option value="4" selected>4 pôles (1500 tr/min)</option>
                                    <option value="6">6 pôles (1000 tr/min)</option>
                                    <option value="8">8 pôles (750 tr/min)</option>
                                </select>
                            </div>
                            
                            <!-- Classe d'efficience cible -->
                            <div class="simulateur-input-group">
                                <label for="classeCible_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg width="24px" height="24px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="si-glyph si-glyph-chart-column" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>872</title> <defs> </defs> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(0.000000, 1.000000)" fill="#434343"> <path d="M16,13.031 L0.984,13.031 L0.984,0.016 L0.027,0.016 L0,13.95 L0.027,13.95 L0.027,13.979 L16,13.95 L16,13.031 Z" class="si-glyph-fill"> </path> <path d="M4.958,7.021 L2.016,7.021 L2.016,11.985 L4.958,11.985 L4.958,7.021 L4.958,7.021 Z" class="si-glyph-fill"> </path> <path d="M9.969,5.047 L7.016,5.047 L7.016,11.969 L9.969,11.969 L9.969,5.047 L9.969,5.047 Z" class="si-glyph-fill"> </path> <path d="M14.953,3.031 L12,3.031 L12,11.978 L14.953,11.978 L14.953,3.031 L14.953,3.031 Z" class="si-glyph-fill"> </path> </g> </g> </g></svg>
                                Classe d'efficience  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button>  <?php echo impactIndicator(85); ?></label>
                                <div class="help-content">
  <div class="info-box">
    <p>Chaque niveau d'amélioration de classe d'efficience (par exemple, passer d'IE2 à IE3) génère des économies d'argent et d'énergie annuelles de 2% à 4%.</p>
  </div>
</div>
                                <select id="classeCible_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="IE1">IE1 (Standard)</option>
                                    <option value="IE2">IE2 (Haut rendement)</option>
                                    <option value="IE3">IE3 (Premium)</option>
                                    <option value="IE4" selected>IE4 (Super Premium)</option>
                                    <option value="IE5">IE5 (Ultra Premium)</option>
                                </select>
                            </div>

                            <div class="simulateur-input-group">
                                <label for="efficaciteMoteurCible_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg fill="#000000" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 491.52 491.52" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M471.04,163.84h-32.768c-8.192,0-16.384,6.144-20.48,14.336l-18.432,81.92l-14.336-40.96 c0-8.192-8.192-14.336-16.384-14.336h-40.96v-20.48c0-12.288-8.192-20.48-20.48-20.48H81.92c-10.24,0-20.48,8.192-20.48,20.48 v163.84c0,2.048,0,6.144,2.048,8.192l28.672,61.44c4.096,8.192,10.24,12.288,18.432,12.288H358.4 c8.192,0,14.336-4.096,18.432-10.24l18.432-30.72h6.144l18.432,49.152c4.096,8.192,10.24,12.288,18.432,12.288h32.768 c12.288,0,20.48-8.192,20.48-20.48V184.32C491.52,172.032,483.328,163.84,471.04,163.84z M458.752,421.888l-24.576-61.44 c-4.096-6.144-10.24-12.288-20.48-12.288h-32.768c-8.192,0-14.336,4.096-18.432,10.24l-16.384,30.72H124.928L102.4,344.064V204.8 h184.32v20.48c0,12.288,8.192,20.48,20.48,20.48h47.104l8.192,26.624c2.048,8.192,10.24,14.336,18.432,14.336h32.768 c8.192,0,16.384-6.144,20.48-14.336l24.576-81.92V421.888z"></path> </g> </g> <g> <g> <path d="M81.92,266.24H20.48C8.192,266.24,0,274.432,0,286.72c0,12.288,10.24,20.48,20.48,20.48h61.44 c12.288,0,20.48-8.192,20.48-20.48C102.4,274.432,94.208,266.24,81.92,266.24z"></path> </g> </g> <g> <g> <path d="M20.48,225.28C8.192,225.28,0,233.472,0,245.76v81.92c0,12.288,8.192,20.48,20.48,20.48c12.288,0,20.48-8.192,20.48-20.48 v-81.92C40.96,233.472,32.768,225.28,20.48,225.28z"></path> </g> </g> <g> <g> <path d="M245.76,102.4h-81.92c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h81.92 c12.288,0,20.48-8.192,20.48-20.48v-61.44C266.24,110.592,258.048,102.4,245.76,102.4z M225.28,163.84h-40.96v-20.48h40.96V163.84 z"></path> </g> </g> <g> <g> <path d="M286.72,40.96H122.88c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h163.84 c12.288,0,20.48-8.192,20.48-20.48V61.44C307.2,49.152,299.008,40.96,286.72,40.96z M266.24,102.4H143.36V81.92h122.88V102.4z"></path> </g> </g> </g></svg>
                                    Efficacité du moteur (%)  <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button>  <?php echo impactIndicator(85); ?></label>
                                    <div class="help-content">
  <div class="info-box">
    <p>Une augmentation de 1% d'efficacité moteur réduit génère des économies d'argent er d'énergie annuelles de 1% à 1,2%. Les moteurs industriels modernes présentent généralement une efficacité entre 80% et 97%.</p>
    <p>Cette valeur est automatiquement déterminée selon :</p>
    <ul>
      <li>La puissance de votre moteur (kW)</li>
      <li>Sa classe d'efficience énergétique (IE1 à IE5)</li>
      <li>Sa vitesse de rotation (nombre de pôles)</li>
    </ul>
  </div>
</div>
                                <input
                                    id="efficaciteMoteurCible_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="10"
                                    max="100"
                                    value="93"
                                    class="simulateur-input"
                                    readonly="readonly"
                                    style="background-color: #fff; cursor: default; opacity: 0.4;"
                                />
                            </div>
                            
                            <!-- Variateur de vitesse -->
                            <div class="simulateur-input-group switch-group">
                            <div class="switch-wrap">
                            <span class="text-bold-black simulateur-label">
                                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12M22 12C22 6.47715 17.5228 2 12 2M22 12H19.5M2 12C2 6.47715 6.47715 2 12 2M2 12H4.5M12 2V4.5M19.0784 5L13.4999 10.5M19.0784 19.0784L18.8745 18.8745C18.1827 18.1827 17.8368 17.8368 17.4331 17.5894C17.0753 17.3701 16.6851 17.2085 16.2769 17.1105C15.8166 17 15.3274 17 14.349 17L9.65096 17C8.6726 17 8.18342 17 7.72307 17.1106C7.31493 17.2086 6.92475 17.3702 6.56686 17.5895C6.1632 17.8369 5.8173 18.1828 5.12549 18.8746L4.92163 19.0784M4.92163 5L6.65808 6.73645M14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                    Variateur de vitesse</span>
                                <div class="switch-container">
                                    <input type="checkbox" id="vitesseVariableCible_<?php echo $simulateurId; ?>" class="switch-input">
                                    <label for="vitesseVariableCible_<?php echo $simulateurId; ?>" class="switch-label" aria-label="Vitesse variable"></label>
                                </div>
                                <button type="button" class="help-icon" aria-label="Afficher l'aide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
        </svg>
    </button>
                                <?php echo impactIndicator(85); ?>
                            </div>
                            <div class="help-content">
  <div class="info-box">
    <p>Permet d'ajuster la vitesse du moteur en fonction des besoins, évitant le fonctionnement constant à pleine puissance.</p>
    <p>Représente l'investissement le plus rentable pour les applications à charge variable comme les pompes générant des d'économies d'argent et d'énergies de 10% à 20% avec un retour sur investissement en 1 à 3 ans.</p>
    <p>À éviter: Pour les applications à charge constante fonctionnant toujours à pleine puissance, un variateur peut réduire légèrement l'efficacité globale (1% à 3%).</p>
    <p><strong>Pour moteurs ≤ 90 kW : Coefficient de réduction = 0,85 (économie de 15%)</strong></p>
    <p><strong>Pour moteurs > 90 kW : Coefficient de réduction = 0,75 (économie de 25%)</strong></p>
  </div>
</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conditions d'exploitation -->
                    <div class="simulateur-full-width">
                    <div class="simulateur-section simulateur-section-special">
                        <h3>Conditions d'exploitation :</h3>
                        
                        <div class="simulateur-inputs">
                        <div class="simulateur-conditions-grid">
                            <div class="simulateur-input-group">
                                <label for="coutEnergie_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg fill="#000000" width="24px" height="24px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M18.605 2.022v0zM18.605 2.022l-2.256 11.856 8.174 0.027-11.127 16.072 2.257-13.043-8.174-0.029zM18.606 0.023c-0.054 0-0.108 0.002-0.161 0.006-0.353 0.028-0.587 0.147-0.864 0.333-0.154 0.102-0.295 0.228-0.419 0.373-0.037 0.043-0.071 0.088-0.103 0.134l-11.207 14.832c-0.442 0.607-0.508 1.407-0.168 2.076s1.026 1.093 1.779 1.099l5.773 0.042-1.815 10.694c-0.172 0.919 0.318 1.835 1.18 2.204 0.257 0.11 0.527 0.163 0.793 0.163 0.629 0 1.145-0.294 1.533-0.825l11.22-16.072c0.442-0.607 0.507-1.408 0.168-2.076-0.34-0.669-1.026-1.093-1.779-1.098l-5.773-0.010 1.796-9.402c0.038-0.151 0.057-0.308 0.057-0.47 0-1.082-0.861-1.964-1.939-1.999-0.024-0.001-0.047-0.001-0.071-0.001v0z"></path> </g></svg>
                                    Prix unitaire de l'électricité (€/kWh)</label>
                                <input
                                    id="coutEnergie_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="0.01"
                                    max="1"
                                    step="0.01"
                                    value="0.15"
                                    class="simulateur-input"
                                />
                            </div>
                            
                            <div class="simulateur-input-group">
                                <label for="joursFonctionnement_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8 2v4"/>
        <path d="M16 2v4"/>
        <rect width="18" height="18" x="3" y="4" rx="2"/>
        <path d="M3 10h18"/>
    </svg>
                                Combien de jours de fonctionnement par an ? (J)</label>
                                <input
                                    id="joursFonctionnement_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="1"
                                    max="365"
                                    value="250"
                                    class="simulateur-input"
                                />
                            </div>
                            
                            <div class="simulateur-input-group">
                                <label for="heuresFonctionnementParJour_<?php echo $simulateurId; ?>" class="text-bold-black">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
    </svg>
                                    Combien d'heures de fonctionnement par jour ? (H)</label>
                                <input
                                    id="heuresFonctionnementParJour_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="1"
                                    max="24"
                                    value="16"
                                    class="simulateur-input"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- COLONNE 2 - Résultats -->
                <div class="simulateur-full-width">
                <div class="simulateur-section simulateur-section-last">
                    <h3 class="text-bold-black simulateur-results-header">Résultats :</h3>
                    
                    <div class="simulateur-results-container">
                        <div class="simulateur-results-summary">
                            <div class="simulateur-result-row" style="flex-direction: column; align-items: flex-start;">
                                <div class="simulateur-result-label"> <button type="button" class="help-icon" aria-label="Afficher l'aide" style="display: inline-flex; vertical-align: middle;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <circle cx="12" cy="12" r="10"></circle>
                        <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
                    </svg>
                </button>
                                    Consommation annuelle actuelle :</div>
                                <div class="simulateur-result-value text-bold-black" id="consommationActuelle_<?php echo $simulateurId; ?>">0 kWh/an</div>
                            </div>
                           
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label"> <button type="button" class="help-icon" aria-label="Afficher l'aide" style="display: inline-flex; vertical-align: middle;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <circle cx="12" cy="12" r="10"></circle>
                        <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
                    </svg>
                </button>
                                    Consommation annuelle cible :</div>
                                <div class="simulateur-result-value text-bold-black" id="consommationCible_<?php echo $simulateurId; ?>">0 kWh/an</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label"> <button type="button" class="help-icon" data-target="economie-help" aria-label="Afficher l'aide" style="display: inline-flex; vertical-align: middle;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
        <circle cx="12" cy="12" r="10"></circle>
        <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
    </svg>
</button>
                                    Économie annuelle :</div>
                                <div class="simulateur-result-value positive text-bold-black" id="economieAnnuelle_<?php echo $simulateurId; ?>">0 €/an</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label"> <button type="button" class="help-icon" data-target="investissement-help" aria-label="Afficher l'aide" style="display: inline-flex; vertical-align: middle;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
        <circle cx="12" cy="12" r="10"></circle>
        <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
    </svg>
</button>
                                    Coût investissement :</div>
                                <div class="simulateur-result-value text-bold-black" id="coutInvestissement_<?php echo $simulateurId; ?>">0 €</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label"> <button type="button" class="help-icon" data-target="retour-help" aria-label="Afficher l'aide" style="display: inline-flex; vertical-align: middle;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
        <circle cx="12" cy="12" r="10"></circle>
        <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
    </svg>
</button>
                                    Retour sur investissement :</div>
                                <div class="simulateur-result-value text-bold-black" id="retourInvestissement_<?php echo $simulateurId; ?>">0 ans</div>
                            </div>
                            <div class="help-content" id="consommation-help">
        <div class="info-box">
            <p>Formule de calcul :</p>
            <p><strong>Consommation (kWh/an) = Puissance moteur (kW) × Jours par an × Heures par jour ÷ Efficacité</strong></p>
            <p>Avec variateur de vitesse :</p>
            <ul style="list-style-type: none; padding: 0 !important;">
                <li>• Moteurs ≤ 90 kW : Consommation réduite de 15%</li>
                <li>• Moteurs > 90 kW : Consommation réduite de 25%</li>
            </ul>
        </div>
    </div>
    <div class="help-content" id="economie-help">
                    <div class="info-box">
                        <p>Formule de calcul :</p>
                        <p><strong>Économie annuelle (€/an) = (Consommation actuelle - Consommation cible) × Prix unitaire de l'électricité (€/kWh)</strong></p>
                    </div>
                </div>

                <div class="help-content" id="investissement-help">
                    <div class="info-box">
                        <p>Formule de calcul :</p>
                        <p><strong>Coût d'investissement (€) = Coût du moteur + Coût du variateur de vitesse (si applicable)</strong></p>
                        <p>Note : Le coût varie selon la puissance et la classe d'efficience du moteur.</p>
                    </div>
                </div>

                <div class="help-content" id="retour-help">
                    <div class="info-box">
                        <p>Formule de calcul :</p>
                        <p><strong>Retour sur investissement (ans) = Coût d'investissement (€) ÷ Économie annuelle (€/an)</strong></p>
                        <p>Plus cette valeur est basse, plus l'investissement est rentable rapidement.</p>
                    </div>
                </div>
                        </div>

                       
                        
                        <div class="simulateur-chart-fullwidth">
                            <h4 class="text-bold-black">Évolution des coûts sur 10 ans :</h4>
                            <canvas id="chartCouts_<?php echo $simulateurId; ?>"></canvas>
                        </div>
                        
                        <div class="simulateur-results-columns">
                        <div class="simulateur-analysis">
                            <h4 class="text-bold-black">Analyse :</h4>
                            <p id="analyseText_<?php echo $simulateurId; ?>">Veuillez ajuster les paramètres pour obtenir une analyse.</p>
                        </div>
                        
                        <div class="simulateur-savings">
                            <h4 class="text-bold-black simulateur-savings-2">
                            <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 3.5C13 2.94772 12.5523 2.5 12 2.5C11.4477 2.5 11 2.94772 11 3.5V4.0592C9.82995 4.19942 8.75336 4.58509 7.89614 5.1772C6.79552 5.93745 6 7.09027 6 8.5C6 9.77399 6.49167 10.9571 7.5778 11.7926C8.43438 12.4515 9.58764 12.8385 11 12.959V17.9219C10.2161 17.7963 9.54046 17.5279 9.03281 17.1772C8.32378 16.6874 8 16.0903 8 15.5C8 14.9477 7.55228 14.5 7 14.5C6.44772 14.5 6 14.9477 6 15.5C6 16.9097 6.79552 18.0626 7.89614 18.8228C8.75336 19.4149 9.82995 19.8006 11 19.9408V20.5C11 21.0523 11.4477 21.5 12 21.5C12.5523 21.5 13 21.0523 13 20.5V19.9435C14.1622 19.8101 15.2376 19.4425 16.0974 18.8585C17.2122 18.1013 18 16.9436 18 15.5C18 14.1934 17.5144 13.0022 16.4158 12.1712C15.557 11.5216 14.4039 11.1534 13 11.039V6.07813C13.7839 6.20366 14.4596 6.47214 14.9672 6.82279C15.6762 7.31255 16 7.90973 16 8.5C16 9.05228 16.4477 9.5 17 9.5C17.5523 9.5 18 9.05228 18 8.5C18 7.09027 17.2045 5.93745 16.1039 5.17721C15.2467 4.58508 14.1701 4.19941 13 4.0592V3.5ZM11 6.07814C10.2161 6.20367 9.54046 6.47215 9.03281 6.8228C8.32378 7.31255 8 7.90973 8 8.5C8 9.22601 8.25834 9.79286 8.79722 10.2074C9.24297 10.5503 9.94692 10.8384 11 10.9502V6.07814ZM13 13.047V17.9263C13.7911 17.8064 14.4682 17.5474 14.9737 17.204C15.6685 16.7321 16 16.1398 16 15.5C16 14.7232 15.7356 14.1644 15.2093 13.7663C14.7658 13.4309 14.0616 13.1537 13 13.047Z" fill="#000000"></path> </g></svg>
                                Économies estimées :</h4>
                            <div class="simulateur-savings-grid">
                                <div class="simulateur-savings-item">
                                    <div class="simulateur-savings-label">Sur 5 ans :</div>
                                    <div class="simulateur-savings-value" id="economie5Ans_<?php echo $simulateurId; ?>">0 €</div>
                                </div>
                                <div class="simulateur-savings-item">
                                    <div class="simulateur-savings-label">Sur 10 ans :</div>
                                    <div class="simulateur-savings-value" id="economie10Ans_<?php echo $simulateurId; ?>">0 €</div>
                                </div>
                                <div class="simulateur-savings-item">
                                    <div class="simulateur-savings-label">Sur 15 ans :</div>
                                    <div class="simulateur-savings-value" id="economie15Ans_<?php echo $simulateurId; ?>">0 €</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="simulateur-environmental">
                            <h4 class="text-bold-black simulateur-environmental-label-2">
                                <svg fill="#000000" height="24px" width="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 291.04 291.04" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M267.556,172.743V33.593c0-4.971-4.029-9-9-9h-57.244c-4.971,0-9,4.029-9,9v8.001 C171.894,21.479,143.89,9.046,113.033,9.046C50.706,9.046,0,59.752,0,122.079c0,62.327,50.706,113.033,113.033,113.033 c20.283,0,39.334-5.374,55.809-14.768c-0.002,0.182-0.016,0.361-0.016,0.543c0,33.695,27.413,61.107,61.107,61.107 c33.694,0,61.107-27.413,61.107-61.107C291.041,201.946,282.348,184.294,267.556,172.743z M187.204,62.739h-40.092 c-5.181-13.108-12.087-24.9-20.467-34.714C151.037,31.539,172.462,44.349,187.204,62.739z M192.311,80.739v32.34h-34.516 c-0.576-11.15-2.229-22.016-4.867-32.34H192.311z M86.306,113.079c0.682-11.145,2.638-22.069,5.693-32.34h42.312 c2.956,10.172,4.825,21.065,5.469,32.34H86.306z M139.751,131.079c-0.685,11.167-2.616,22.089-5.624,32.34H91.83 c-2.976-10.165-4.865-21.057-5.527-32.34H139.751z M113.112,39.864c5.637,6.615,10.493,14.337,14.472,22.875h-28.78 C102.777,54.23,107.585,46.49,113.112,39.864z M99.563,28.005c-8.362,9.901-15.264,21.697-20.457,34.734H38.862 C53.633,44.313,75.112,31.49,99.563,28.005z M27.473,80.739h45.758c-2.682,10.353-4.365,21.221-4.953,32.34H18.429 C19.514,101.586,22.65,90.681,27.473,80.739z M27.473,163.419c-4.823-9.942-7.959-20.847-9.044-32.34h49.857 c0.593,11.157,2.263,22.023,4.917,32.34H27.473z M38.862,181.419h40.194c5.223,13.137,12.183,24.941,20.628,34.751 C75.184,212.71,53.658,199.875,38.862,181.419z M98.602,181.419h28.808c-3.942,8.543-8.715,16.293-14.204,22.901 C107.517,197.709,102.617,189.978,98.602,181.419z M175.014,194.059c-13.359,11.519-29.946,19.387-48.201,22.05 c8.281-9.854,15.115-21.635,20.256-34.69h36.237C180.023,185.29,177.229,189.527,175.014,194.059z M152.876,163.419 c2.645-10.338,4.312-21.205,4.905-32.34h34.531v32.34H152.876z M229.934,263.995c-23.77,0-43.107-19.338-43.107-43.107 c0-14.538,7.266-28.003,19.437-36.021c2.527-1.665,4.049-4.489,4.049-7.516V42.593h39.244v134.759 c0,3.027,1.521,5.851,4.049,7.516c12.171,8.018,19.437,21.483,19.437,36.021C273.041,244.657,253.703,263.995,229.934,263.995z"></path> <path d="M238.934,199.583V60.431c0-4.971-4.029-9-9-9s-9,4.029-9,9v139.152c-8.185,3.498-13.921,11.62-13.921,21.084 c0,12.659,10.262,22.92,22.921,22.92c12.658,0,22.92-10.262,22.92-22.92C252.854,211.203,247.118,203.081,238.934,199.583z"></path> </g> </g> </g> </g></svg>
                            Impact environnemental :</h4>
                            <div class="simulateur-environmental-grid">
                                <div class="simulateur-environmental-row">
                                    <div class="simulateur-environmental-label">
                                    <svg fill="#000000" height="24px" width="24px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 490 490" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M470,360.75h-79.6c-8.1,0-15.4-4.8-18.5-12.3c-3.1-7.4-1.5-16,4.2-21.8l59.2-60.2c1.1-1.7,2.2-4.2,2.2-7.4 c0-7.5-6.1-13.6-13.6-13.6s-13.6,6.1-13.6,13.6c0,11.1-9,20-20,20c-11.1,0-20-9-20-20c0-29.6,24.1-53.6,53.6-53.6 s53.6,24.1,53.6,53.6c0,11.8-3.8,23.1-10.9,32.5c-0.5,0.7-1.1,1.3-1.7,1.9l-26.8,27.2H470c11.1,0,20,9,20,20 C490,351.85,481.1,360.75,470,360.75z"></path> </g> <g> <path d="M271.8,321.75c-53.1,0-96.2-43.2-96.2-96.2c0-53.1,43.2-96.2,96.2-96.2c53.1,0,96.2,43.2,96.2,96.2 S324.9,321.75,271.8,321.75z M271.8,169.35c-31,0-56.2,25.2-56.2,56.2s25.2,56.2,56.2,56.2s56.2-25.2,56.2-56.2 C328,194.55,302.8,169.35,271.8,169.35z"></path> </g> <g> <path d="M94.3,317.85c-52,0-94.3-42.3-94.3-94.3s42.3-94.3,94.3-94.3c29.8,0,58.1,14.3,75.8,38.3c6.6,8.9,4.7,21.4-4.2,28 s-21.4,4.7-28-4.2c-10.4-14-26.3-22-43.6-22c-29.9,0-54.2,24.3-54.2,54.2s24.3,54.2,54.2,54.2c15.3,0,29.5-6.2,39.9-17.5 c7.5-8.1,20.2-8.7,28.3-1.2c8.1,7.5,8.7,20.2,1.2,28.3C145.8,306.75,120.6,317.85,94.3,317.85z"></path> </g> </g> </g> </g></svg>
                                    <button type="button" class="help-icon" aria-label="Afficher l'aide">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
            </svg>
        </button>
                                    Réduction annuelle de CO2 :</div>
        <div class="simulateur-environmental-value text-bold-black" id="reductionCO2_<?php echo $simulateurId; ?>">0 kg CO2/an</div>
    </div>
    
    <div class="help-content">
        <div class="info-box">
        <p>Formule de calcul :</p>
            <p><strong>Réduction CO2 (kg/an) = Économie d'énergie (kWh/an) × 0,275 kg CO2/kWh</strong></p>
            <p>Le facteur 0,275 représente l'émission moyenne de CO2 par kWh d'électricité produit en Europe.</p>
        </div>
        </div>
    </div>
                                <div class="simulateur-environmental-row">
                                    <div class="simulateur-environmental-label">
                                  <svg version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css">  .st0{fill:#000000;}  </style> <g> <path class="st0" d="M354.07,158.485l-213.094,70.516c-0.641,0.219-1.188,0.547-1.813,0.797c-0.922,0.391-1.859,0.734-2.719,1.219 c-0.766,0.406-1.438,0.922-2.156,1.406c-0.719,0.5-1.469,0.969-2.141,1.531c-0.688,0.594-1.297,1.25-1.938,1.891 c-0.578,0.594-1.172,1.172-1.703,1.813c-0.547,0.688-1.016,1.438-1.5,2.156c-0.469,0.734-0.969,1.422-1.375,2.188 c-0.391,0.75-0.688,1.547-1.016,2.328c-0.344,0.813-0.703,1.625-0.969,2.484c-0.25,0.828-0.391,1.719-0.578,2.594 c-0.156,0.828-0.359,1.656-0.453,2.516c-0.094,0.984-0.047,1.984-0.047,2.969c0.016,0.672-0.094,1.297-0.031,1.984 c0.016,0.141,0.063,0.266,0.078,0.422c0.094,0.859,0.313,1.719,0.484,2.578c0.188,0.969,0.344,1.938,0.625,2.844 c0.047,0.125,0.047,0.266,0.094,0.375c0.406,1.234,0.938,2.406,1.484,3.547c0.156,0.313,0.328,0.625,0.5,0.938 c0.688,1.266,1.469,2.453,2.328,3.594c0.328,0.406,0.688,0.766,1.031,1.156c0.719,0.828,1.469,1.609,2.281,2.328 c0.422,0.375,0.859,0.75,1.297,1.094c0.844,0.656,1.719,1.266,2.625,1.813c0.469,0.281,0.906,0.578,1.391,0.844 c1.203,0.625,2.469,1.188,3.766,1.656c0.203,0.063,0.375,0.172,0.578,0.234c1.547,0.5,3.125,0.813,4.75,1.031 c0.391,0.063,0.797,0.078,1.203,0.109c0.781,0.078,1.594,0.188,2.391,0.188c0.5,0,0.984-0.094,1.484-0.109 c0.266-0.031,0.5,0.016,0.75,0.016c11.188-0.938,20.891,2.547,29.688,10.641c5.797,5.344,10.594,12.156,14.328,19.234h-10.656 c-11.938,0-21.594,9.672-21.594,21.609v66.5c0,11.922,9.656,21.594,21.594,21.594h19.141c0,2.281,0,5.063,0,8.203 c-4.328,0-7.828,3.516-7.828,7.844s3.5,7.844,7.828,7.844c0,2.578,0,5.203,0,7.828c-4.328,0-7.828,3.516-7.828,7.828 c0,4.328,3.5,7.844,7.828,7.844c0,7.016,0,13.078,0,16.859c0,15.672,9.406,17.234,25.078,17.234c0,0,3.141,9.406,32.906,9.406 c29.781,0,32.906-9.406,32.906-9.406c15.672,0,25.063-1.563,25.063-17.234c0-3.813,0-9.922,0-17.016 c3.578-0.734,6.281-3.906,6.281-7.688s-2.703-6.953-6.281-7.672c0-2.719,0-5.469,0-8.141c3.578-0.734,6.281-3.906,6.281-7.688 c0-3.797-2.703-6.953-6.281-7.672c0-3.234,0-6.047,0-8.375h20.719c11.938,0,21.594-9.672,21.594-21.594v-66.5 c0-11.938-9.656-21.609-21.594-21.609h-9.453c5.656-12.547,13.969-23.469,25.188-29.578c13.078-7.141,17.906-23.531,10.766-36.625 s-23.547-17.906-36.609-10.781c-30.719,16.75-48.203,46.906-56.531,76.984H253.82c-5.703-18.375-15.75-37.688-29.953-52.984 l147.172-48.688c14.156-4.688,21.828-19.953,17.141-34.109C383.508,161.47,368.227,153.798,354.07,158.485z"></path> <path class="st0" d="M149.445,194.892c2.813,0,5.672-0.438,8.484-1.359l213.109-70.531c14.156-4.688,21.828-19.938,17.141-34.109 c-4.672-14.156-19.938-21.828-34.109-17.141l-213.094,70.516c-14.172,4.688-21.844,19.938-17.156,34.094 C127.57,187.704,138.117,194.892,149.445,194.892z"></path> <path class="st0" d="M167.305,103.36c2.813,0,5.672-0.453,8.484-1.375l149.188-49.359c14.141-4.688,21.828-19.953,17.141-34.109 S322.18-3.312,308.008,1.376L158.836,50.735c-14.172,4.688-21.844,19.953-17.156,34.094 C145.43,96.173,155.977,103.36,167.305,103.36z"></path> </g> </g></svg>
                                  <button type="button" class="help-icon" aria-label="Afficher l'aide">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">ℹ</text>
            </svg>
        </button>
                                  Économie d'énergie annuelle :</div>
    <div class="simulateur-environmental-value text-bold-black" id="economieEnergie_<?php echo $simulateurId; ?>">0 kWh/an</div>
</div>

<div class="help-content">
    <div class="info-box">
        <p>Formule de calcul :</p>
        <p><strong>Économie d'énergie (kWh/an) = Consommation moteur actuel - Consommation moteur cible</strong></p>
</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simulateur-disclaimer">
    <p>Note: Ce simulateur utilise des valeurs approximatives basées sur les normes d'efficacité IE1 à IE5 pour les moteurs électriques. Les résultats réels peuvent varier en fonction des spécifications exactes du moteur et des conditions d'utilisation. Pour une analyse détaillée, consultez un spécialiste.</p>
</div>
                <script>
document.addEventListener('DOMContentLoaded', function() {
    const simulateurId = '<?php echo $simulateurId; ?>';
    const simulateurData = <?php echo json_encode($simulateurData); ?>;
    
    // Éléments DOM pour l'accordéon actuel
    
    const toggleAccordionActuel = document.getElementById(`toggleAccordionActuel_${simulateurId}`);
    const accordionActuel = document.getElementById(`accordionActuel_${simulateurId}`);
    const accordionIconActuel = document.getElementById(`accordionIconActuel_${simulateurId}`);
    const puissancesActuelContainer = document.getElementById(`puissancesActuel_${simulateurId}`);
    
    // Éléments DOM pour l'accordéon cible
    const toggleAccordionCible = document.getElementById(`toggleAccordionCible_${simulateurId}`);
    const accordionCible = document.getElementById(`accordionCible_${simulateurId}`);
    const accordionIconCible = document.getElementById(`accordionIconCible_${simulateurId}`);
    const puissancesCibleContainer = document.getElementById(`puissancesCible_${simulateurId}`);
   

    const puissanceCategories = {
    micro: [0.12, 0.18, 0.20, 0.25, 0.37, 0.4, 0.55, 0.75],
    petit: [1.1, 1.5, 2.2, 3, 4, 5.5, 7.5, 11],
    moyen: [15, 18.5, 22, 30, 37, 45, 55, 75],
    grand: [90, 110, 132, 160, 200, 250, 315, 355, 400, 450, 500, 1000]
};
    
    // Valeurs par défaut
    let puissanceActuelle = 11;
    let puissanceCible = 11;

    function normalizeNumber(value) {
        if (typeof value === 'string') {
            value = value.replace(',', '.');
        }
        return parseFloat(value);
    }

    
    function generatePuissanceButtons(category, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Vider le conteneur
        
        // Créer les boutons pour chaque valeur dans la catégorie
        puissanceCategories[category].forEach(puissance => {
            const button = document.createElement('button');
            button.className = 'simulateur-puissance-btn';
            
            // Si cette puissance est la puissance actuellement sélectionnée, ajouter la classe 'selected'
            if (puissance === (containerId.includes('Actuelle') ? puissanceActuelle : puissanceCible)) {
                button.classList.add('selected');
            }
            
            // Formater l'affichage pour les petites puissances
            const displayValue = puissance < 1 ? puissance.toFixed(2).replace('0.', '.') : puissance;
            button.textContent = `${parseFloat(puissance).toFixed(puissance % 1 === 0 ? 0 : 2)} kW`;
            button.dataset.value = puissance;
            
            // Ajouter l'écouteur d'événements pour la sélection
            button.addEventListener('click', function() {
                // Désélectionner tous les boutons de ce conteneur
                document.querySelectorAll(`#${containerId} .simulateur-puissance-btn`).forEach(btn => {
                    btn.classList.remove('selected');
                });
                
                // Sélectionner ce bouton
                this.classList.add('selected');
                
                // Mettre à jour la valeur et recalculer
                if (containerId.includes('Actuelle')) {
                    selectPuissanceActuelle(puissance);
                } else {
                    selectPuissanceCible(puissance);
                }
            });
            
            // Ajouter le bouton au conteneur
            container.appendChild(button);
        });
    }
    
    function getMotorEfficiency(power, poles, efficiencyClass, simulateurData) {
  // Convertir power en nombre pour s'assurer qu'il s'agit bien d'un nombre
  const powerNum = parseFloat(power);
  
  // Déterminer la plage de vitesse
  let speedRange;
  switch (parseInt(poles)) {
    case 2: speedRange = '1801_6000'; break; // 3000 tr/min
    case 4: speedRange = '1201_1800'; break; // 1500 tr/min
    case 6: speedRange = '901_1200'; break;  // 1000 tr/min
    case 8: speedRange = '600_900'; break;   // 750 tr/min
    default: speedRange = '1201_1800';       // Par défaut 4 pôles
  }
  
  // Cas spéciaux pour les petites puissances - utiliser des comparaisons numériques
  // au lieu de comparaisons de chaînes
  if (powerNum <= 1.1) {
    // IE1
    if (efficiencyClass === "IE1") {
      if (Math.abs(powerNum - 0.12) < 0.001) {
        if (poles == 2) return "45.0";
        if (poles == 4) return "50.0";
        if (poles == 6) return "38.3";
        if (poles == 8) return "31.0";
      }
      else if (Math.abs(powerNum - 0.18) < 0.001) {
        if (poles == 2) return "52.8";
        if (poles == 4) return "57.0";
        if (poles == 6) return "45.5";
        if (poles == 8) return "38.0";
      }
      else if (Math.abs(powerNum - 0.20) < 0.001) {
        if (poles == 2) return "54.6";
        if (poles == 4) return "58.5";
        if (poles == 6) return "47.6";
        if (poles == 8) return "39.7";
      }
      else if (Math.abs(powerNum - 0.25) < 0.001) {
        if (poles == 2) return "58.2";
        if (poles == 4) return "61.5";
        if (poles == 6) return "52.1";
        if (poles == 8) return "43.4";
      }
      else if (Math.abs(powerNum - 0.37) < 0.001) {
        if (poles == 2) return "63.9";
        if (poles == 4) return "66.0";
        if (poles == 6) return "59.7";
        if (poles == 8) return "49.7";
      }
      else if (Math.abs(powerNum - 0.40) < 0.001) {
        if (poles == 2) return "64.9";
        if (poles == 4) return "66.8";
        if (poles == 6) return "61.1";
        if (poles == 8) return "50.9";
      }
      else if (Math.abs(powerNum - 0.55) < 0.001) {
        if (poles == 2) return "69.0";
        if (poles == 4) return "70.0";
        if (poles == 6) return "65.8";
        if (poles == 8) return "56.1";
      }
      else if (Math.abs(powerNum - 0.75) < 0.001) {
        if (poles == 2) return "72.1";
        if (poles == 4) return "72.1";
        if (poles == 6) return "70.0";
        if (poles == 8) return "61.2";
      }
      else if (Math.abs(powerNum - 1.1) < 0.001) {
        if (poles == 2) return "75.0";
        if (poles == 4) return "75.0";
        if (poles == 6) return "72.9";
        if (poles == 8) return "66.5";
      }
    }
    // IE2
    else if (efficiencyClass === "IE2") {
      if (Math.abs(powerNum - 0.12) < 0.001) {
        if (poles == 2) return "53.6";
        if (poles == 4) return "59.1";
        if (poles == 6) return "50.6";
        if (poles == 8) return "39.8";
      }
      else if (Math.abs(powerNum - 0.18) < 0.001) {
        if (poles == 2) return "60.4";
        if (poles == 4) return "64.7";
        if (poles == 6) return "56.6";
        if (poles == 8) return "45.9";
      }
      else if (Math.abs(powerNum - 0.20) < 0.001) {
        if (poles == 2) return "61.9";
        if (poles == 4) return "65.9";
        if (poles == 6) return "58.2";
        if (poles == 8) return "47.4";
      }
      else if (Math.abs(powerNum - 0.25) < 0.001) {
        if (poles == 2) return "64.8";
        if (poles == 4) return "68.5";
        if (poles == 6) return "61.6";
        if (poles == 8) return "50.6";
      }
      else if (Math.abs(powerNum - 0.37) < 0.001) {
        if (poles == 2) return "69.5";
        if (poles == 4) return "72.7";
        if (poles == 6) return "67.6";
        if (poles == 8) return "56.1";
      }
      else if (Math.abs(powerNum - 0.40) < 0.001) {
        if (poles == 2) return "70.4";
        if (poles == 4) return "73.5";
        if (poles == 6) return "68.8";
        if (poles == 8) return "57.2";
      }
      else if (Math.abs(powerNum - 0.55) < 0.001) {
        if (poles == 2) return "74.1";
        if (poles == 4) return "77.1";
        if (poles == 6) return "73.1";
        if (poles == 8) return "61.7";
      }
      else if (Math.abs(powerNum - 0.75) < 0.001) {
        if (poles == 2) return "77.4";
        if (poles == 4) return "79.6";
        if (poles == 6) return "75.9";
        if (poles == 8) return "66.2";
      }
      else if (Math.abs(powerNum - 1.1) < 0.001) {
        if (poles == 2) return "79.6";
        if (poles == 4) return "81.4";
        if (poles == 6) return "78.1";
        if (poles == 8) return "70.8";
      }
    }
    // IE3
    else if (efficiencyClass === "IE3") {
      if (Math.abs(powerNum - 0.12) < 0.001) {
        if (poles == 2) return "60.8";
        if (poles == 4) return "64.8";
        if (poles == 6) return "57.7";
        if (poles == 8) return "50.7";
      }
      else if (Math.abs(powerNum - 0.18) < 0.001) {
        if (poles == 2) return "65.9";
        if (poles == 4) return "69.9";
        if (poles == 6) return "63.9";
        if (poles == 8) return "58.7";
      }
      else if (Math.abs(powerNum - 0.20) < 0.001) {
        if (poles == 2) return "67.2";
        if (poles == 4) return "71.1";
        if (poles == 6) return "65.4";
        if (poles == 8) return "60.6";
      }
      else if (Math.abs(powerNum - 0.25) < 0.001) {
        if (poles == 2) return "69.7";
        if (poles == 4) return "73.5";
        if (poles == 6) return "68.6";
        if (poles == 8) return "64.1";
      }
      else if (Math.abs(powerNum - 0.37) < 0.001) {
        if (poles == 2) return "73.8";
        if (poles == 4) return "77.3";
        if (poles == 6) return "73.5";
        if (poles == 8) return "69.3";
      }
      else if (Math.abs(powerNum - 0.40) < 0.001) {
        if (poles == 2) return "74.6";
        if (poles == 4) return "78.0";
        if (poles == 6) return "74.4";
        if (poles == 8) return "70.1";
      }
      else if (Math.abs(powerNum - 0.55) < 0.001) {
        if (poles == 2) return "77.8";
        if (poles == 4) return "80.8";
        if (poles == 6) return "77.2";
        if (poles == 8) return "73.0";
      }
      else if (Math.abs(powerNum - 0.75) < 0.001) {
        if (poles == 2) return "80.7";
        if (poles == 4) return "82.5";
        if (poles == 6) return "78.9";
        if (poles == 8) return "75.0";
      }
      else if (Math.abs(powerNum - 1.1) < 0.001) {
        if (poles == 2) return "82.7";
        if (poles == 4) return "84.1";
        if (poles == 6) return "81.0";
        if (poles == 8) return "77.7";
      }
    }
    // IE4
    else if (efficiencyClass === "IE4") {
      if (Math.abs(powerNum - 0.12) < 0.001) {
        if (poles == 2) return "66.5";
        if (poles == 4) return "69.8";
        if (poles == 6) return "64.9";
        if (poles == 8) return "62.3";
      }
      else if (Math.abs(powerNum - 0.18) < 0.001) {
        if (poles == 2) return "70.8";
        if (poles == 4) return "74.7";
        if (poles == 6) return "70.1";
        if (poles == 8) return "67.2";
      }
      else if (Math.abs(powerNum - 0.20) < 0.001) {
        if (poles == 2) return "71.9";
        if (poles == 4) return "75.8";
        if (poles == 6) return "71.4";
        if (poles == 8) return "68.4";
      }
      else if (Math.abs(powerNum - 0.25) < 0.001) {
        if (poles == 2) return "74.3";
        if (poles == 4) return "77.9";
        if (poles == 6) return "74.1";
        if (poles == 8) return "70.8";
      }
      else if (Math.abs(powerNum - 0.37) < 0.001) {
        if (poles == 2) return "78.1";
        if (poles == 4) return "81.1";
        if (poles == 6) return "78.0";
        if (poles == 8) return "74.8";
      }
      else if (Math.abs(powerNum - 0.40) < 0.001) {
        if (poles == 2) return "78.9";
        if (poles == 4) return "81.7";
        if (poles == 6) return "78.7";
        if (poles == 8) return "74.9";
      }
      else if (Math.abs(powerNum - 0.55) < 0.001) {
        if (poles == 2) return "81.5";
        if (poles == 4) return "83.9";
        if (poles == 6) return "80.9";
        if (poles == 8) return "77.0";
      }
      else if (Math.abs(powerNum - 0.75) < 0.001) {
        if (poles == 2) return "83.5";
        if (poles == 4) return "85.7";
        if (poles == 6) return "82.7";
        if (poles == 8) return "78.4";
      }
      else if (Math.abs(powerNum - 1.1) < 0.001) {
        if (poles == 2) return "85.2";
        if (poles == 4) return "87.2";
        if (poles == 6) return "84.5";
        if (poles == 8) return "80.8";
      }
    }
    // IE5
    else if (efficiencyClass === "IE5") {
      if (Math.abs(powerNum - 0.12) < 0.001) {
        if (poles == 2) return "71.4";
        if (poles == 4) return "74.3";
        if (poles == 6) return "69.8";
        if (poles == 8) return "67.4";
      }
      else if (Math.abs(powerNum - 0.18) < 0.001) {
        if (poles == 2) return "75.2";
        if (poles == 4) return "78.7";
        if (poles == 6) return "74.6";
        if (poles == 8) return "71.9";
      }
      else if (Math.abs(powerNum - 0.20) < 0.001) {
        if (poles == 2) return "76.2";
        if (poles == 4) return "79.6";
        if (poles == 6) return "75.7";
        if (poles == 8) return "73.0";
      }
      else if (Math.abs(powerNum - 0.25) < 0.001) {
        if (poles == 2) return "78.3";
        if (poles == 4) return "81.5";
        if (poles == 6) return "78.1";
        if (poles == 8) return "75.2";
      }
      else if (Math.abs(powerNum - 0.37) < 0.001) {
        if (poles == 2) return "81.7";
        if (poles == 4) return "84.3";
        if (poles == 6) return "81.6";
        if (poles == 8) return "78.4";
      }
      else if (Math.abs(powerNum - 0.40) < 0.001) {
        if (poles == 2) return "82.3";
        if (poles == 4) return "84.8";
        if (poles == 6) return "82.2";
        if (poles == 8) return "78.9";
      }
      else if (Math.abs(powerNum - 0.55) < 0.001) {
        if (poles == 2) return "84.6";
        if (poles == 4) return "86.7";
        if (poles == 6) return "84.2";
        if (poles == 8) return "80.6";
      }
      else if (Math.abs(powerNum - 0.75) < 0.001) {
        if (poles == 2) return "86.3";
        if (poles == 4) return "88.2";
        if (poles == 6) return "85.7";
        if (poles == 8) return "82.0";
      }
      else if (Math.abs(powerNum - 1.1) < 0.001) {
        if (poles == 2) return "87.8";
        if (poles == 4) return "89.5";
        if (poles == 6) return "87.2";
        if (poles == 8) return "84.0";
      }
    }
  }
  
  // Si ce n'est pas un cas spécial, utiliser la méthode standard
  try {
    // Convertir le nombre en chaîne pour l'accès aux données
    const powerStr = powerNum.toString();
    
    if (simulateurData.rendements[efficiencyClass][powerStr] &&
        simulateurData.rendements[efficiencyClass][powerStr][speedRange]) {
      const efficiency = simulateurData.rendements[efficiencyClass][powerStr][speedRange];
      return (efficiency * 100).toFixed(1);
    }
  } catch (e) {
    console.error("Erreur lors de l'accès aux données de rendement:", e);
  }
  
  // Si pas trouvé, chercher la puissance la plus proche dans le tableau
  const powers = Object.keys(simulateurData.rendements[efficiencyClass])
                       .map(Number)
                       .sort((a, b) => a - b);
  
  let closestPower = powers[0];
  let minDiff = Math.abs(powerNum - closestPower);
  
  for (let i = 1; i < powers.length; i++) {
    const diff = Math.abs(powerNum - powers[i]);
    if (diff < minDiff) {
      minDiff = diff;
      closestPower = powers[i];
    }
  }
  
  console.log(`Utilisation de la puissance proche ${closestPower} pour ${powerNum} kW, ${poles} pôles, ${efficiencyClass}`);
  
  // Utiliser la puissance la plus proche
  const efficiency = simulateurData.rendements[efficiencyClass][closestPower.toString()][speedRange];
  return (efficiency * 100).toFixed(1);
}

// Fonction à appeler lors des changements de paramètres pour mettre à jour l'efficacité
function mettreAJourEfficaciteMoteur() {
    
    // Pour le moteur actuel
    const polesActuel = document.getElementById(`polesActuel_${simulateurId}`).value;
    const classeActuelle = document.getElementById(`classeActuelle_${simulateurId}`).value;
    const efficaciteActuelle = getMotorEfficiency(puissanceActuelle, polesActuel, classeActuelle, simulateurData);
    document.getElementById(`efficaciteMoteurActuel_${simulateurId}`).value = efficaciteActuelle;
    
    // Pour le moteur cible
    const polesCible = document.getElementById(`polesCible_${simulateurId}`).value;
    const classeCible = document.getElementById(`classeCible_${simulateurId}`).value;
    const efficaciteCible = getMotorEfficiency(puissanceCible, polesCible, classeCible, simulateurData);
    document.getElementById(`efficaciteMoteurCible_${simulateurId}`).value = efficaciteCible;
}

// Ajouter des événements d'écoute pour mettre à jour l'efficacité
document.getElementById(`polesActuel_${simulateurId}`).addEventListener('change', mettreAJourEfficaciteMoteur);
document.getElementById(`classeActuelle_${simulateurId}`).addEventListener('change', mettreAJourEfficaciteMoteur);
document.getElementById(`polesCible_${simulateurId}`).addEventListener('change', mettreAJourEfficaciteMoteur);
document.getElementById(`classeCible_${simulateurId}`).addEventListener('change', mettreAJourEfficaciteMoteur);

// Également mettre à jour lors des changements de puissance
function selectPuissanceActuelle(puissance) {
    puissanceActuelle = parseFloat(puissance);
    document.getElementById(`puissanceActuelleValue_${simulateurId}`).textContent = puissance + ' kW';
    mettreAJourEfficaciteMoteur(); // Mettre à jour l'efficacité
    calculerResultats();
}

function selectPuissanceCible(puissance) {
    puissanceCible = parseFloat(puissance);
    document.getElementById(`puissanceCibleValue_${simulateurId}`).textContent = puissance + ' kW';
    mettreAJourEfficaciteMoteur(); // Mettre à jour l'efficacité
    calculerResultats();
}


    const categorySelectActuel = document.getElementById(`puissanceCategoryActuelle_${simulateurId}`);
    generatePuissanceButtons(categorySelectActuel.value, `puissanceActuelleGrid_${simulateurId}`);
    
    // Écouter les changements de catégorie pour le moteur actuel
    categorySelectActuel.addEventListener('change', function() {
        generatePuissanceButtons(this.value, `puissanceActuelleGrid_${simulateurId}`);
    });
    
    // Faire de même pour le moteur cible si nécessaire
    const categorySelectCible = document.getElementById(`puissanceCategoryCible_${simulateurId}`);
    if (categorySelectCible) {
        generatePuissanceButtons(categorySelectCible.value, `puissanceCibleGrid_${simulateurId}`);
        
        categorySelectCible.addEventListener('change', function() {
            generatePuissanceButtons(this.value, `puissanceCibleGrid_${simulateurId}`);
        });
    }


    // Modification de la génération d'analyse textuelle
function genererAnalyseTexte(economieAnnuelle, retourInvestissement, classeCible, puissanceCible, vitesseVariableCible) {
    let analyseTexte = '';
    let analyseIcon = '';
    
    if (economieAnnuelle <= 0) {
        analyseIcon = '❌';
        analyseTexte = "Aucune économie significative n'est générée avec cette configuration.";
    } else if (retourInvestissement <= 2) {
        analyseIcon = '🔥';
        analyseTexte = `Investissement très rentable à court terme. Économies importantes et retour sur investissement rapide de ${retourInvestissement.toFixed(1)} ans.`;
    } else if (retourInvestissement <= 5) {
        analyseIcon = '✅';
        analyseTexte = `Bon investissement. Rentabilité atteinte en moins de 5 ans (${retourInvestissement.toFixed(1)} ans).`;
    } else if (retourInvestissement <= 10) {
        analyseIcon = '⚠️';
        analyseTexte = `Rentabilité à moyen terme. Économies modérées sur ${retourInvestissement.toFixed(1)} ans.`;
    } else {
        analyseIcon = '🔍';
        analyseTexte = `Rentabilité à long terme. Envisager d'autres options ou attendre une hausse du coût de l'énergie.`;
    }
    
    // Ajouter les détails techniques
    if (economieAnnuelle > 0) {
        analyseTexte += ` L'investissement dans un moteur ${classeCible} de ${puissanceCible} kW`;
        analyseTexte += vitesseVariableCible ? ' avec variateur de vitesse' : '';
        analyseTexte += ` est une solution technique adaptée pour ce cas d'usage.`;
    }
    
    return `<span class="analyse-icon">${analyseIcon}</span><span class="analyse-text">${analyseTexte}</span>`;
}

    // Fonction pour calculer les résultats
    function calculerResultats() {
        // Récupérer toutes les valeurs des champs
        const classeActuelle = document.getElementById(`classeActuelle_${simulateurId}`).value;
        const classeCible = document.getElementById(`classeCible_${simulateurId}`).value;
        const polesActuel = parseInt(document.getElementById(`polesActuel_${simulateurId}`).value);
        const polesCible = parseInt(document.getElementById(`polesCible_${simulateurId}`).value);
        const vitesseVariableActuel = document.getElementById(`vitesseVariableActuel_${simulateurId}`).checked;
        const vitesseVariableCible = document.getElementById(`vitesseVariableCible_${simulateurId}`).checked;
        const coutEnergie = parseFloat(document.getElementById(`coutEnergie_${simulateurId}`).value);
        const joursFonctionnement = parseInt(document.getElementById(`joursFonctionnement_${simulateurId}`).value);
        const heuresFonctionnementParJour = parseInt(document.getElementById(`heuresFonctionnementParJour_${simulateurId}`).value);
        const efficaciteMoteurActuel = parseInt(document.getElementById(`efficaciteMoteurActuel_${simulateurId}`).value) / 100;
        const efficaciteMoteurCible = parseInt(document.getElementById(`efficaciteMoteurCible_${simulateurId}`).value) / 100;
        

        function findClosestPower(targetPower, rendements, classe) {
  // Convertir targetPower en nombre
  targetPower = parseFloat(targetPower);
  
  // Convertir les clés en nombres pour la comparaison
  const powers = Object.keys(rendements[classe]).map(key => parseFloat(key)).sort((a, b) => a - b);
  
  // Voir si la valeur exacte existe (avec une petite tolérance)
  for (let power of powers) {
    if (Math.abs(power - targetPower) < 0.0001) {
      return power.toString(); // Retourne la clé d'origine
    }
  }
  
  // Trouver la puissance la plus proche
  let closestPower = powers[0];
  let minDiff = Math.abs(targetPower - closestPower);
  
  for (let i = 1; i < powers.length; i++) {
    const diff = Math.abs(targetPower - powers[i]);
    if (diff < minDiff) {
      minDiff = diff;
      closestPower = powers[i];
    }
  }
  
  console.log(`Puissance ${targetPower} non trouvée, utilisation de ${closestPower} à la place`);
  return closestPower.toString(); // Retourne la clé d'origine
}



    function getVitesseRange(poles) {
    switch(poles) {
        case 2: return '1801_6000'; // 3000 tr/min
        case 4: return '1201_1800'; // 1500 tr/min
        case 6: return '901_1200';  // 1000 tr/min
        case 8: return '600_900';   // 750 tr/min
        default: return '1201_1800'; // Par défaut 4 pôles
    }
}

         const puissanceActuelleAjustee = Number(findClosestPower(Number(puissanceActuelle), simulateurData.rendements, classeActuelle));
         const puissanceCibleAjustee = findClosestPower(puissanceCible, simulateurData.rendements, classeCible);
         
         const plageVitesseActuelle = getVitesseRange(polesActuel);
         const plageVitesseCible = getVitesseRange(polesCible);
        // Calculer les rendements ajustés
        const rendementActuel = simulateurData.rendements[classeActuelle][puissanceActuelleAjustee][plageVitesseActuelle];
        const rendementCible = simulateurData.rendements[classeCible][puissanceCibleAjustee][plageVitesseCible];

        console.log("Puissance recherchée:", puissanceActuelle);
console.log("Puissance utilisée:", puissanceActuelleAjustee);
console.log("Plage de vitesse:", plageVitesseActuelle);
console.log("Rendement trouvé:", rendementActuel);
console.log("Classe d'efficience:", classeActuelle);
        
        // Calculer les heures de fonctionnement annuelles
        const heuresAnnuelles = joursFonctionnement * heuresFonctionnementParJour;
        
        // Calculer les consommations
        const puissanceUtileActuelle = puissanceActuelleAjustee * efficaciteMoteurActuel;
        let consommationActuelle = puissanceActuelleAjustee * heuresAnnuelles / efficaciteMoteurActuel;

        if (vitesseVariableActuel) {
    // Appliquer la réduction variable selon la puissance
    if (puissanceActuelleAjustee <= 90) {
        consommationActuelle *= 0.85; // Réduction de 15% pour ≤ 90 kW
    } else {
        consommationActuelle *= 0.75; // Réduction de 25% pour > 90 kW
    }
}

// Calculer la consommation avec le moteur cible
        const puissanceUtileCible = puissanceCibleAjustee * efficaciteMoteurCible;
        let consommationCible = puissanceCibleAjustee * heuresAnnuelles / efficaciteMoteurCible;
        
        // Ajuster la consommation si un variateur est utilisé (exemple: réduction de 15%)
        if (vitesseVariableCible) {
    if (puissanceCibleAjustee <= 90) {
        consommationCible *= 0.85; // Réduction de 15% pour ≤ 90 kW
    } else {
        consommationCible *= 0.75; // Réduction de 25% pour > 90 kW
    }
}
        
        // Économie d'énergie annuelle
        const economieEnergie = consommationActuelle - consommationCible;
        
        // Économie financière annuelle
        const economieAnnuelle = economieEnergie * coutEnergie;
        
        // Coût d'investissement
        // Coût d'investissement
        let coutInvestissement = 0;

// Vérifier si la puissance exacte existe dans le tableau des coûts
if (simulateurData.coutMoteurs[classeCible][puissanceCible] !== undefined) {
    coutInvestissement = simulateurData.coutMoteurs[classeCible][puissanceCible];
} else {
    // Sinon, trouver la puissance la plus proche
    const puissances = Object.keys(simulateurData.coutMoteurs[classeCible]).map(Number);
    const puissanceProche = puissances.reduce((a, b) => {
        return Math.abs(b - puissanceCible) < Math.abs(a - puissanceCible) ? b : a;
    });
    coutInvestissement = simulateurData.coutMoteurs[classeCible][puissanceProche];
}

// Ajouter le coût du variateur si nécessaire
if (vitesseVariableCible && !vitesseVariableActuel) {
    if (simulateurData.coutVSD[puissanceCible] !== undefined) {
        coutInvestissement += simulateurData.coutVSD[puissanceCible];
    } else {
        // Utiliser la puissance la plus proche disponible
        const puissancesVSD = Object.keys(simulateurData.coutVSD).map(Number);
        const puissanceVSDProche = puissancesVSD.reduce((a, b) => {
            return Math.abs(b - puissanceCible) < Math.abs(a - puissanceCible) ? b : a;
        });
        coutInvestissement += simulateurData.coutVSD[puissanceVSDProche];
    }
}

// Retour sur investissement
// Retour sur investissement
let retourInvestissement = 0;

if (economieAnnuelle > 0) {
    // Cas rentable : économie positive
    retourInvestissement = coutInvestissement / economieAnnuelle;
} else if (economieAnnuelle < 0) {
    // Cas non rentable avec perte : économie négative
    retourInvestissement = coutInvestissement / economieAnnuelle; // Donnera une valeur négative
} else {
    // Si économie = 0
    retourInvestissement = 0; // Au lieu de Infinity, mettez 0
}
        
        // Économies sur plusieurs années
        const economie5Ans = economieAnnuelle * 5;
        const economie10Ans = economieAnnuelle * 10;
        const economie15Ans = economieAnnuelle * 15;
        
        // Impact environnemental (kg CO2 par kWh - moyenne européenne ~0.275)
        const facteurCO2 = 0.275;
        const reductionCO2 = economieEnergie * facteurCO2;
        
        // Mettre à jour les affichages
        document.getElementById(`consommationActuelle_${simulateurId}`).textContent = Math.round(consommationActuelle).toLocaleString() + ' kWh/an';
        document.getElementById(`consommationCible_${simulateurId}`).textContent = Math.round(consommationCible).toLocaleString() + ' kWh/an';
        document.getElementById(`economieAnnuelle_${simulateurId}`).textContent = Math.round(economieAnnuelle).toLocaleString() + ' €/an';
        document.getElementById(`coutInvestissement_${simulateurId}`).textContent = Math.round(coutInvestissement).toLocaleString() + ' €';
        const retourInvestissementElement = document.getElementById(`retourInvestissement_${simulateurId}`);
retourInvestissementElement.textContent = retourInvestissement.toFixed(1) + ' ans';

// Appliquer les couleurs
if (retourInvestissement < 0) {
    retourInvestissementElement.style.color = '#e31206'; // Rouge pour les valeurs négatives
} else if (retourInvestissement === 0) {
    retourInvestissementElement.style.color = '#000000'; // Noir pour zéro
} else {
    retourInvestissementElement.style.color = '#0D8538'; // Vert pour les valeurs positives
}
        document.getElementById(`economie5Ans_${simulateurId}`).textContent = Math.round(economie5Ans).toLocaleString() + ' €';
        document.getElementById(`economie10Ans_${simulateurId}`).textContent = Math.round(economie10Ans).toLocaleString() + ' €';
        document.getElementById(`economie15Ans_${simulateurId}`).textContent = Math.round(economie15Ans).toLocaleString() + ' €';
        document.getElementById(`reductionCO2_${simulateurId}`).textContent = Math.round(reductionCO2).toLocaleString() + ' kg CO2/an';
        document.getElementById(`economieEnergie_${simulateurId}`).textContent = Math.round(economieEnergie).toLocaleString() + ' kWh/an';
        
       
const economieAnnuelleElement = document.getElementById(`economieAnnuelle_${simulateurId}`);
if (economieAnnuelle < 0) {
    economieAnnuelleElement.style.color = '#e31206';
} else if (economieAnnuelle === 0) {
    economieAnnuelleElement.style.color = '#000000';
} else {
    economieAnnuelleElement.style.color = '#0D8538';
}
        
        document.getElementById(`analyseText_${simulateurId}`).innerHTML = genererAnalyseTexte(
    economieAnnuelle,
    retourInvestissement,
    classeCible,
    puissanceCible,
    vitesseVariableCible
);


        
        // Mettre à jour le graphique
        updateChart(economieAnnuelle, coutInvestissement);
    }
    
    // Fonction pour mettre à jour le graphique
   // Fonction modifiée pour mettre à jour le graphique
function updateChart(economieAnnuelle, coutInvestissement) {
    const ctx = document.getElementById(`chartCouts_${simulateurId}`);
    
    // Détruire le graphique existant s'il existe
    if (window.coutChart) {
        window.coutChart.destroy();
    }
    
    // Calculer les données pour le graphique
    const labels = Array.from({length: 11}, (_, i) => i);
    const dataActuel = labels.map(annee => 0); // Référence à 0 (pas de changement)
    
    // Économies cumulées: démarre à -investissement et augmente de economieAnnuelle chaque année
    const dataEconomiesCumulees = labels.map(annee => -coutInvestissement + (annee * economieAnnuelle));
    
    // Calculer le point de rentabilité (quand économies cumulées = 0)
    const pointRentabilite = economieAnnuelle > 0 ? coutInvestissement / economieAnnuelle : 0;
    
    // Options d'annotation pour le point de rentabilité
    const annotations = {};
    
    // N'ajouter l'annotation que si le point de rentabilité est dans la période de 10 ans
    if (pointRentabilite > 0 && pointRentabilite <= 10) {
        annotations.pointRentabilite = {
            type: 'line',
            xMin: pointRentabilite,
            xMax: pointRentabilite,
            borderColor: '#2563eb',
            borderWidth: 2,
            borderDash: [5, 5],
            label: {
                content: `Rentabilité: ${pointRentabilite.toFixed(1)} ans`,
                enabled: true,
                position: 'top'
            }
        };
    }
    
    // Créer le nouveau graphique
    window.coutChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pas d\'investissement (référence)',
                    data: dataActuel,
                    borderColor: '#e31206',
                    backgroundColor: 'rgba(227, 18, 6, 0.1)',
                    fill: true
                },
                {
                    label: 'Économies cumulées',
                    data: dataEconomiesCumulees,
                    borderColor: '#0D8538',
                    backgroundColor: 'rgba(22, 163, 74, 0.15)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Années'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Économies (€)'
                    },
                    grid: {
                        color: function(context) {
                            if (context.tick.value === 0) {
                                return '#2563eb'; // Ligne verte pour zéro (point de rentabilité)
                            }
                            return '#e5e7eb'; // Couleur par défaut
                        },
                        lineWidth: function(context) {
                            if (context.tick.value === 0) {
                                return 2; // Épaisseur accrue pour la ligne de rentabilité
                            }
                            return 1; // Épaisseur par défaut
                        }
                    }
                }
            },
            plugins: {
                annotation: {
                    annotations: annotations
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            if (context.datasetIndex === 1) { // Économies cumulées
                                if (value < 0) {
                                    return `Investissement restant à amortir: ${Math.abs(value).toLocaleString()} €`;
                                } else {
                                    return `Économies nettes: ${value.toLocaleString()} €`;
                                }
                            }
                            return context.dataset.label;
                        }
                    }
                }
            }
        }
    });
}
    
    // Ajouter des écouteurs d'événements pour les autres champs
    const champs = [
        'polesActuel', 'classeActuelle', 'polesCible', 'classeCible',
        'coutEnergie', 'joursFonctionnement', 'heuresFonctionnementParJour', 'efficaciteMoteurActuel', 'efficaciteMoteurCible'
    ];
    
    champs.forEach(champ => {
        const element = document.getElementById(`${champ}_${simulateurId}`);
        if (element) {
            element.addEventListener('change', calculerResultats);
        }
    });
    
    // Gérer le variateur de vitesse
   // Gérer les variateurs de vitesse
const vitesseVariableActuelElement = document.getElementById(`vitesseVariableActuel_${simulateurId}`);
if (vitesseVariableActuelElement) {
    vitesseVariableActuelElement.addEventListener('change', calculerResultats);
}

const vitesseVariableCibleElement = document.getElementById(`vitesseVariableCible_${simulateurId}`);
if (vitesseVariableCibleElement) {
    vitesseVariableCibleElement.addEventListener('change', calculerResultats);
}
    // Calculer les résultats initiaux
    mettreAJourEfficaciteMoteur();
    calculerResultats();
});

// Gestion des infobulles
const helpIcons = document.querySelectorAll('.help-icon');
    
// Ajouter un gestionnaire d'événement à chaque bouton d'aide
helpIcons.forEach(icon => {
    icon.addEventListener('click', function() {
    // Si l'icône a un attribut data-target, utiliser cet ID pour trouver l'infobulle spécifique
    const targetId = this.getAttribute('data-target');
    if (targetId) {
      const helpContent = document.getElementById(targetId);
      if (helpContent && helpContent.classList.contains('help-content')) {
        helpContent.classList.toggle('visible');
        this.classList.toggle('active');
        return;
      }
    }
    
    // Rechercher d'abord dans .simulateur-input-group
    let helpContent = this.closest('.simulateur-input-group')?.querySelector('.help-content');
    
    // Si non trouvé, chercher dans le parent direct ou dans le document
    if (!helpContent) {
      const parentRow = this.closest('.simulateur-environmental-row');
      if (parentRow) {
        // Chercher après cet élément (frère suivant)
        helpContent = parentRow.nextElementSibling;
        if (!helpContent.classList.contains('help-content')) {
          helpContent = null;
        }
      }
    }
    
    // Si toujours pas trouvé et c'est dans les résultats, rechercher l'infobulle globale des résultats
    if (!helpContent && this.closest('.simulateur-results-summary')) {
      helpContent = this.closest('.simulateur-results-container').querySelector('.help-content');
    }
    
    // Basculer la classe 'visible' pour afficher/masquer le contenu
    if (helpContent) {
      helpContent.classList.toggle('visible');
      this.classList.toggle('active');
    }
  });
});



// Empêcher le focus automatique sur les inputs quand on clique sur les labels et autres éléments
document.querySelectorAll('.simulateur-input-group label:not(.switch-label), .simulateur-input-group .help-icon, .simulateur-input-group span:not(.simulateur-label)').forEach(element => {
  element.addEventListener('click', function(e) {
    // Ne pas interférer avec les clics sur les éléments de switch
    if (this.closest('.switch-group') || this.closest('.switch-container') || this.classList.contains('switch-label')) {
      return;
    }
    
    // Empêcher la propagation du clic aux parents pour les autres éléments
    e.stopPropagation();
    
    // Empêcher le comportement par défaut pour les éléments non-switch
    if (!this.classList.contains('help-icon')) {
      e.preventDefault();
    }
  });
});

</script>