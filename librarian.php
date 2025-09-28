$books = [];
$sql_select_books = "SELECT book_id, title, author, isbn, publication_year, quantity, available_copies FROM books";
$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $like_query = "%" . $search_query . "%";
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
    $param_types .= 'sss';
}

if (!empty($where_clauses)) {
    $sql_select_books .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_select_books .= " ORDER BY book_id ASC";


if (!empty($params)) {
    if ($stmt = $conn->prepare($sql_select_books)) {
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
} else {
    $result = $conn->query($sql_select_books);
}

if (isset($result) && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

$conn->close();
?>