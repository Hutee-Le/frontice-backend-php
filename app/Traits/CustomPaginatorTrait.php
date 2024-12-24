<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait CustomPaginatorTrait
{
    public function customPaginate(Request $request, $query = null)
    {
        $perPage = $request->get('per_page', 10); // Số lượng bản ghi mỗi trang, mặc định là 10
        $page = $request->get('page', 1);

        // Truy vấn cơ sở dữ liệu
        if ($query === null) {
            $query = $this->query(); // Nếu truy vấn không truyền vào, thì trả về mô hình hiện tại
        }

        // Lấy thông tin sắp xếp từ request
        $sortColumn = $request->get('sort_column', 'title'); // Bỏ mặc định
        $filter = $request->get('filter', null);
        $sortOrder = $request->get('sort', null); // Bỏ mặc định
        $search = $request->get('search', null); // Tham số tìm kiếm

        try
        // Thực hiện tìm kiếm nếu có
        {
            if ($search) {
                $query->where(function ($q) use ($search, $sortColumn) {
                    // Giả sử bạn tìm kiếm trong các cột 'title' và 'description'
                    $q->where($sortColumn, 'like', '%' . $search . '%');
                });
            }
            if (is_array($filter)) {
                if (in_array('point', $filter)) {
                    $minPoint = request()->input('min_point', 0);
                    $maxPoint = request()->input('max_point', PHP_INT_MAX);
                    $query->whereBetween('point', [$minPoint, $maxPoint])->orderBy('point');
                }
                if (in_array('join_total', $filter)) {
                    $minParticipation = request()->input('min_join', 0);
                    $maxParticipation = request()->input('max_join', PHP_INT_MAX);
                    $query->withCount('solutions')
                        ->havingBetween('solutions_count', [$minParticipation, $maxParticipation])->orderBy('solutions_count');
                } elseif (in_array('submitted_total', $filter)) {
                    $minParticipation = request()->input('min_submitted', 0);
                    $maxParticipation = request()->input('max_submitted', PHP_INT_MAX);
                    $query->withCount('submitted')
                        ->havingBetween('submitted_count', [$minParticipation, $maxParticipation])->orderBy('submitted_count');
                }
                if (in_array('owner', $filter)) {
                    $owner = request()->get('owner', null);
                    if ($owner) {
                        if (is_array($owner))
                            $query->whereIn('admin_id', $owner);
                        else $query->where('admin_id', $owner);
                    }
                }
                if (in_array('level', $filter)) {
                    $level = request()->get('level', null);
                    if ($level) {
                        if (is_array($level))
                            $query->whereIn('level_id', $level);
                        else $query->where('level_id', $level);
                    }
                }
                if (in_array('created_at', $filter)) {
                    $start = request()->get('start', $query->min('created_at'));
                    $start = is_numeric($start) ? Carbon::parse(date('Y-m-d H:i:s', $start)) : Carbon::parse($start);

                    // Lấy giá trị 'end' từ request hoặc dùng thời gian hiện tại nếu không có 'end'
                    $end = request()->get('end', now());
                    $end = is_numeric($end) ? Carbon::parse(date('Y-m-d H:i:s', $end)) : Carbon::parse($end);
                    $query->whereBetween('created_at', [$start, $end]);
                }
                if (in_array('premium', $filter)) {
                    $query->where('premium', true);
                }
            }
            // Kiểm tra nếu có yêu cầu sắp xếp
            if ($sortOrder === 'newest') {
                $query->orderBy('created_at', 'desc'); // Sắp xếp theo ngày tạo mới nhất
            } elseif ($sortOrder === 'oldest') {
                $query->orderBy('created_at', 'asc'); // Sắp xếp theo ngày tạo cũ nhất
            } elseif ($sortOrder === 'asc_joined') {
                $query->withCount('solutions')
                    ->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_joined') {
                $query->withCount('solutions')
                    ->orderByDesc('solutions_count');
            } elseif ($sortOrder === 'asc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderByDesc('solutions_count');
            } elseif ($sortColumn && $sortOrder) {
                // Sắp xếp theo A-Z hoặc Z-A
                if ($sortOrder === 'az') {
                    $query->orderBy($sortColumn, 'asc'); // Sắp xếp A-Z
                } elseif ($sortOrder === 'za') {
                    $query->orderBy($sortColumn, 'desc'); // Sắp xếp Z-A
                }
            }
        } catch (\Exception $_) {
        }
        // Nếu không có sắp xếp nào, dữ liệu sẽ giữ nguyên thứ tự ban đầu.

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy bản ghi cho trang hiện tại
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Thêm thông tin bổ sung
        $paginator->appends($request->except('page'));

        return $paginator;
    }
    public function TaskeePaginate(Request $request, $query = null)
    {
        $perPage = $request->get('per_page', 10); // Số lượng bản ghi mỗi trang, mặc định là 10
        $page = $request->get('page', 1);

        // Truy vấn cơ sở dữ liệu
        if ($query === null) {
            $query = $this->query(); // Nếu truy vấn không truyền vào, thì trả về mô hình hiện tại
        }

        // Lấy thông tin sắp xếp từ request
        $sortColumn = $request->get('sort_column', 'firstname'); // Bỏ mặc định
        $filter = $request->get('filter', null);
        $sortOrder = $request->get('sort', null); // Bỏ mặc định
        $search = $request->get('search', null); // Tham số tìm kiếm

        try
        // Thực hiện tìm kiếm nếu có
        {
            if ($search) {
                $query->where(function ($q) use ($search, $sortColumn) {
                    // Giả sử bạn tìm kiếm trong các cột 'title' và 'description'
                    $q->where($sortColumn, 'like', '%' . $search . '%');
                });
            }
            if (is_array($filter)) {
                if (in_array('point', $filter)) {
                    $minPoint = request()->input('min_point', 0);
                    $maxPoint = request()->input('max_point', PHP_INT_MAX);
                    $query->whereBetween('points', [$minPoint, $maxPoint])->orderBy('points');
                }
                if (in_array('premium', $filter)) {
                    $premium = request()->get('premium', 1);
                    if ($premium == true) {
                        $query->whereNotNull('gold_expired')->where('gold_expired', '>', now());
                    } elseif ($premium == false) {
                        $query->whereNotNull('gold_expired')->Where('gold_expired', '<', now());
                    }
                }
                if (in_array('created_at', $filter)) {
                    $start = request()->get('start', $query->min('created_at'));
                    $start = is_numeric($start) ? Carbon::parse(date('Y-m-d H:i:s', $start)) : Carbon::parse($start);

                    // Lấy giá trị 'end' từ request hoặc dùng thời gian hiện tại nếu không có 'end'
                    $end = request()->get('end', now());
                    $end = is_numeric($end) ? Carbon::parse(date('Y-m-d H:i:s', $end)) : Carbon::parse($end);
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
            // Kiểm tra nếu có yêu cầu sắp xếp
            if ($sortOrder === 'newest') {
                $query->orderBy('created_at', 'desc'); // Sắp xếp theo ngày tạo mới nhất
            } elseif ($sortOrder === 'oldest') {
                $query->orderBy('created_at', 'asc'); // Sắp xếp theo ngày tạo cũ nhất
            } elseif ($sortOrder === 'asc_joined') {
                $query->withCount('solutions')
                    ->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_joined') {
                $query->withCount('solutions')
                    ->orderByDesc('solutions_count');
            } elseif ($sortOrder === 'asc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderByDesc('solutions_count');
            } elseif ($sortColumn && $sortOrder) {
                // Sắp xếp theo A-Z hoặc Z-A
                if ($sortOrder === 'az') {
                    $query->orderBy($sortColumn, 'asc'); // Sắp xếp A-Z
                } elseif ($sortOrder === 'za') {
                    $query->orderBy($sortColumn, 'desc'); // Sắp xếp Z-A
                }
            }
        } catch (\Exception $_) {
        }
        // Nếu không có sắp xếp nào, dữ liệu sẽ giữ nguyên thứ tự ban đầu.

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy bản ghi cho trang hiện tại
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Thêm thông tin bổ sung
        $paginator->appends($request->except('page'));

        return $paginator;
    }
    public function AdminPaginate(Request $request, $query = null)
    {
        $perPage = $request->get('per_page', 10); // Số lượng bản ghi mỗi trang, mặc định là 10
        $page = $request->get('page', 1);

        // Truy vấn cơ sở dữ liệu
        if ($query === null) {
            $query = $this->query(); // Nếu truy vấn không truyền vào, thì trả về mô hình hiện tại
        }

        // Lấy thông tin sắp xếp từ request
        $sortColumn = $request->get('sort_column', 'fullname'); // Bỏ mặc định
        $filter = $request->get('filter', null);
        $sortOrder = $request->get('sort', null); // Bỏ mặc định
        $search = $request->get('search', null); // Tham số tìm kiếm

        try
        // Thực hiện tìm kiếm nếu có
        {
            if ($search) {
                $query->where(function ($q) use ($search, $sortColumn) {
                    // Giả sử bạn tìm kiếm trong các cột 'title' và 'description'
                    $q->where($sortColumn, 'like', '%' . $search . '%');
                });
            }
            if (is_array($filter)) {
                if (in_array('role', $filter)) {
                    $role = request()->input('role', null);
                    if ($role) {
                        if (is_array($role))
                            $query->whereIn('role', $role);
                        else
                            $query->where('role', $role);
                    }
                }
                if (in_array('created_at', $filter)) {
                    $start = request()->get('start', $query->min('created_at'));
                    $start = is_numeric($start) ? Carbon::parse(date('Y-m-d H:i:s', $start)) : Carbon::parse($start);

                    // Lấy giá trị 'end' từ request hoặc dùng thời gian hiện tại nếu không có 'end'
                    $end = request()->get('end', now());
                    $end = is_numeric($end) ? Carbon::parse(date('Y-m-d H:i:s', $end)) : Carbon::parse($end);
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
            // Kiểm tra nếu có yêu cầu sắp xếp
            if ($sortOrder === 'newest') {
                $query->orderBy('created_at', 'desc'); // Sắp xếp theo ngày tạo mới nhất
            } elseif ($sortOrder === 'oldest') {
                $query->orderBy('created_at', 'asc'); // Sắp xếp theo ngày tạo cũ nhất
            } elseif ($sortOrder === 'asc_joined') {
                $query->withCount('solutions')
                    ->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_joined') {
                $query->withCount('solutions')
                    ->orderByDesc('solutions_count');
            } elseif ($sortOrder === 'asc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderByDesc('solutions_count');
            } elseif ($sortColumn && $sortOrder) {
                // Sắp xếp theo A-Z hoặc Z-A
                if ($sortOrder === 'az') {
                    $query->orderBy($sortColumn, 'asc'); // Sắp xếp A-Z
                } elseif ($sortOrder === 'za') {
                    $query->orderBy($sortColumn, 'desc'); // Sắp xếp Z-A
                }
            }
        } catch (\Exception $_) {
        }
        // Nếu không có sắp xếp nào, dữ liệu sẽ giữ nguyên thứ tự ban đầu.

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy bản ghi cho trang hiện tại
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Thêm thông tin bổ sung
        $paginator->appends($request->except('page'));

        return $paginator;
    }
    public function TaskPaginate(Request $request, $query = null)
    {
        $perPage = $request->get('per_page', 10); // Số lượng bản ghi mỗi trang, mặc định là 10
        $page = $request->get('page', 1);

        // Truy vấn cơ sở dữ liệu
        if ($query === null) {
            $query = $this->query(); // Nếu truy vấn không truyền vào, thì trả về mô hình hiện tại
        }

        // Lấy thông tin sắp xếp từ request
        $sortColumn = $request->get('sort_column', 'title'); // Bỏ mặc định
        $filter = $request->get('filter', null);
        $sortOrder = $request->get('sort', null); // Bỏ mặc định
        $search = $request->get('search', null); // Tham số tìm kiếm
        $status = $request->get('status', null);
        try
        // Thực hiện tìm kiếm nếu có
        {
            if ($search) {
                $query->where(function ($q) use ($search, $sortColumn) {
                    // Giả sử bạn tìm kiếm trong các cột 'title' và 'description'
                    $q->where($sortColumn, 'like', '%' . $search . '%');
                });
            }
            if ($status) {
                if ($status == 'new') {
                    $query->where('expired', ">=", now());
                } elseif ($status == 'old') {
                    $query->where('expired', "<", now());
                }
            }
            $technical = request()->get('technical', null);
            if (!is_null($technical) && is_array($technical)) {
                $query->where(function ($subQuery) use ($technical) {
                    foreach ($technical as $tech) {
                        $subQuery->orWhereJsonContains('technical->technical', $tech);
                    }
                });
            }
            if (is_array($filter)) {
                if (in_array('required_point', $filter)) {
                    $minPoint = request()->input('min_required_point', 0);
                    $maxPoint = request()->input('max_required_point', PHP_INT_MAX);
                    $query->whereBetween('required_point', [$minPoint, $maxPoint])->orderBy('required_point');
                }
                if (in_array('join_total', $filter)) {
                    $minParticipation = request()->input('min_join', 0);
                    $maxParticipation = request()->input('max_join', PHP_INT_MAX);
                    $query->withCount('solutions')
                        ->havingBetween('solutions_count', [$minParticipation, $maxParticipation])->orderBy('solutions_count');
                } elseif (in_array('submitted_total', $filter)) {
                    $minParticipation = request()->input('min_submitted', 0);
                    $maxParticipation = request()->input('max_submitted', PHP_INT_MAX);
                    $query->withCount('submitted')
                        ->havingBetween('submitted_count', [$minParticipation, $maxParticipation])->orderBy('submitted_count');
                }
                if (in_array('owner', $filter)) {
                    $owner = request()->get('owner', null);
                    if ($owner) {
                        if (is_array($owner))
                            $query->whereIn('tasker_id', $owner);
                        else $query->where('tasker_id', $owner);
                    }
                }
                if (in_array('created_at', $filter)) {
                    $start = request()->get('start', $query->min('created_at'));
                    $start = is_numeric($start) ? Carbon::parse(date('Y-m-d H:i:s', $start)) : Carbon::parse($start);

                    // Lấy giá trị 'end' từ request hoặc dùng thời gian hiện tại nếu không có 'end'
                    $end = request()->get('end', now());
                    $end = is_numeric($end) ? Carbon::parse(date('Y-m-d H:i:s', $end)) : Carbon::parse($end);
                    $query->whereBetween('created_at', [$start, $end]);
                }
            }
            // Kiểm tra nếu có yêu cầu sắp xếp
            if ($sortOrder === 'newest') {
                $query->orderBy('created_at', 'desc'); // Sắp xếp theo ngày tạo mới nhất
            } elseif ($sortOrder === 'oldest') {
                $query->orderBy('created_at', 'asc'); // Sắp xếp theo ngày tạo cũ nhất
            } elseif ($sortOrder === 'asc_joined') {
                $query->withCount('solutions')
                    ->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_joined') {
                $query->withCount('solutions')
                    ->orderByDesc('solutions_count');
            } elseif ($sortOrder === 'asc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderByDesc('solutions_count');
            } elseif ($sortColumn && $sortOrder) {
                // Sắp xếp theo A-Z hoặc Z-A
                if ($sortOrder === 'az') {
                    $query->orderBy($sortColumn, 'asc'); // Sắp xếp A-Z
                } elseif ($sortOrder === 'za') {
                    $query->orderBy($sortColumn, 'desc'); // Sắp xếp Z-A
                }
            }
        } catch (\Exception $_) {
        }
        // Nếu không có sắp xếp nào, dữ liệu sẽ giữ nguyên thứ tự ban đầu.

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy bản ghi cho trang hiện tại
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Thêm thông tin bổ sung
        $paginator->appends($request->except('page'));

        return $paginator;
    }
    public function SolutionPaginate(Request $request, $query = null)
    {
        $perPage = $request->get('per_page', 10); // Số lượng bản ghi mỗi trang, mặc định là 10
        $page = $request->get('page', 1);

        // Truy vấn cơ sở dữ liệu
        if ($query === null) {
            $query = $this->query(); // Nếu truy vấn không truyền vào, thì trả về mô hình hiện tại
        }

        // Lấy thông tin sắp xếp từ request
        $sortColumn = $request->get('sort_column', 'title'); // Bỏ mặc định
        $filter = $request->get('filter', null);
        $sortOrder = $request->get('sort', null); // Bỏ mặc định
        $search = $request->get('search', null); // Tham số tìm kiếm
        try
        // Thực hiện tìm kiếm nếu có
        {
            if ($search) {
                $query->where(function ($q) use ($search, $sortColumn) {
                    // Giả sử bạn tìm kiếm trong các cột 'title' và 'description'
                    $q->where($sortColumn, 'like', '%' . $search . '%');
                });
            }
            if (is_array($filter)) {
                if (in_array('comment', $filter)) {
                    $minParticipation = request()->input('min_comment', 0);
                    $maxParticipation = request()->input('max_comment', PHP_INT_MAX);
                    $query->withCount('comments')
                        ->havingBetween('comments_count', [$minParticipation, $maxParticipation])->orderBy('comments_count');
                } elseif (in_array('like', $filter)) {
                    $minParticipation = request()->input('min_like', 0);
                    $maxParticipation = request()->input('max_like', PHP_INT_MAX);
                    $query->withCount('like')
                        ->havingBetween('like_count', [$minParticipation, $maxParticipation])->orderBy('like_count');
                } elseif (in_array('dislike', $filter)) {
                    $minParticipation = request()->input('min_dislike', 0);
                    $maxParticipation = request()->input('max_dislike', PHP_INT_MAX);
                    $query->withCount('dislike')
                        ->havingBetween('dislike_count', [$minParticipation, $maxParticipation])->orderBy('dislike_count');
                }
                if (in_array('level', $filter)) {
                    $level = request()->get('level', null);
                    if ($level) {
                        if (is_array($level))
                            $query->with('challenge.level')
                                ->whereHas('challenge.level', function ($query) use ($level) {
                                    $query->whereIn('id', $level);
                                })
                            ;
                        else $query->with('challenge.level')
                            ->whereHas('challenge.level', function ($query) use ($level) {
                                $query->where('id', $level);
                            });
                    }
                }
                if (in_array('submited_at', $filter)) {
                    $start = request()->get('start', $query->min('created_at'));
                    $start = is_numeric($start) ? Carbon::parse(date('Y-m-d H:i:s', $start)) : Carbon::parse($start);

                    // Lấy giá trị 'end' từ request hoặc dùng thời gian hiện tại nếu không có 'end'
                    $end = request()->get('end', now());
                    $end = is_numeric($end) ? Carbon::parse(date('Y-m-d H:i:s', $end)) : Carbon::parse($end);
                    $query->whereBetween('submitted_at', [$start, $end]);
                }
                if (in_array('mentor_feedback', $filter)) {
                    $query->whereNotNull('mentor_feedback');
                }
                if (in_array('no_feedback', $filter)) {
                    $query->whereNull('mentor_feedback');
                }
            }
            // Kiểm tra nếu có yêu cầu sắp xếp
            if ($sortOrder === 'newest') {
                $query->orderBy('created_at', 'desc'); // Sắp xếp theo ngày tạo mới nhất
            } elseif ($sortOrder === 'oldest') {
                $query->orderBy('created_at', 'asc'); // Sắp xếp theo ngày tạo cũ nhất
            } elseif ($sortOrder === 'asc_joined') {
                $query->withCount('solutions')
                    ->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_joined') {
                $query->withCount('solutions')
                    ->orderByDesc('solutions_count');
            } elseif ($sortOrder === 'asc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderBy('solutions_count');
            } elseif ($sortOrder === 'desc_submitted') {
                $query->withCount(['solutions' => function ($query) {
                    $query->whereNotNull('submitted_at');  // Lọc các bài nộp có trường submitted_at không null
                }])->orderByDesc('solutions_count');
            } elseif ($sortColumn && $sortOrder) {
                // Sắp xếp theo A-Z hoặc Z-A
                if ($sortOrder === 'az') {
                    $query->orderBy($sortColumn, 'asc'); // Sắp xếp A-Z
                } elseif ($sortOrder === 'za') {
                    $query->orderBy($sortColumn, 'desc'); // Sắp xếp Z-A
                }
            }
        } catch (\Exception $_) {
        }
        // Nếu không có sắp xếp nào, dữ liệu sẽ giữ nguyên thứ tự ban đầu.

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy bản ghi cho trang hiện tại
        $results = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Thêm thông tin bổ sung
        $paginator->appends($request->except('page'));

        return $paginator;
    }

    public static function customPaginateStatic(Request $request, $query = null)
    {
        $instance = new static; // Tạo thể hiện mới của lớp hiện tại
        return $instance->customPaginate($request, $query);
    }
    public static function customSolutionPaginate(Request $request, $query = null)
    {
        $instance = new static; // Tạo thể hiện mới của lớp hiện tại
        return $instance->SolutionPaginate($request, $query);
    }
    public static function customTaskPaginate(Request $request, $query = null)
    {
        $instance = new static; // Tạo thể hiện mới của lớp hiện tại
        return $instance->TaskPaginate($request, $query);
    }
    public static function customTaskeePaginate(Request $request, $query = null)
    {
        $instance = new static; // Tạo thể hiện mới của lớp hiện tại
        return $instance->TaskeePaginate($request, $query);
    }
    public static function customAdminPaginate(Request $request, $query = null)
    {
        $instance = new static; // Tạo thể hiện mới của lớp hiện tại
        return $instance->AdminPaginate($request, $query);
    }
}
