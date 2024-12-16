<?php
if (!function_exists('filterAndSort')) {
    function filterAndSort($data, $filter = null, $sortOrder = null, $toSort = 'title')
    {
        // Áp dụng bộ lọc nếu $filter không phải là null
        $filteredData = $filter ? array_filter($data, function ($item) use ($filter, $toSort) {
            return strpos($item[$toSort], $filter) !== false;
        }) : $data;

        // Sắp xếp dữ liệu nếu $sortOrder không phải là null
        if ($sortOrder) {
            usort($filteredData, function ($a, $b) use ($sortOrder, $toSort) {
                switch ($sortOrder) {
                    case 'az':
                        return strcmp($a[$toSort], $b[$toSort]); // A-Z
                    case 'za':
                        return strcmp($b[$toSort], $a[$toSort]); // Z-A
                    case 'newest':
                        return strtotime($b['created_at']) - strtotime($a['created_at']); // Mới nhất
                    case 'oldest':
                        return strtotime($a['created_at']) - strtotime($b['created_at']); // Cũ nhất
                    default:
                        return 0;
                }
            });
        }

        return $filteredData;
    }
}
