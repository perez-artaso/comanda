<?php 

include_once "ProductManagement.php";

function load_products() {
    load_subcategories();
    load_cuisine_products();
    load_bakery_products();
    load_brewery_products();
    load_bar_products();
}

function load_subcategories () {

    ProductManagement::create_subcategory(
        "Minutas",
        3
    );

    ProductManagement::create_subcategory(
        "Pescados",
        3
    );

    ProductManagement::create_subcategory(
        "Pastas",
        3
    );

    ProductManagement::create_subcategory(
        "Salsas",
        3
    );

    ProductManagement::create_subcategory(
        "Bebidas sin alcohol",
        3
    );

    ProductManagement::create_subcategory(
        "Carnes",
        3
    );

    ProductManagement::create_subcategory(
        "Ensaladas",
        3
    );

    ProductManagement::create_subcategory(
        "Tartas",
        3
    );

    ProductManagement::create_subcategory(
        "Tortas",
        4
    );

    ProductManagement::create_subcategory(
        "Otros",
        4
    );

    ProductManagement::create_subcategory(
        "Cervezas artesanales",
        5
    );

    ProductManagement::create_subcategory(
        "Tragos",
        6
    );

    ProductManagement::create_subcategory(
        "Vinos",
        6
    );

}

function load_cuisine_products() {
    ProductManagement::create_product(
        "Milanesa napolitana con guarnición",
        "350",
        "1",
        "3"
    );

    ProductManagement::create_product(
        "Milanesa maryland con guarnición",
        "400",
        "1",
        "3"
    );

    ProductManagement::create_product(
        "Milanesa con guarnición",
        "300",
        "1",
        "3"
    );

    ProductManagement::create_product(
        "Brotola a la romana",
        "420",
        "2",
        "3"
    );

    ProductManagement::create_product(
        "Porción de rabas",
        "450",
        "2",
        "3"
    );

    ProductManagement::create_product(
        "Filet de merluza con puré de papa",
        "380",
        "2",
        "3"
    );

    ProductManagement::create_product(
        "Tallarines con salsa a elección",
        "250",
        "3",
        "3"
    );

    ProductManagement::create_product(
        "Ñoquis con salsa a elección",
        "250",
        "3",
        "3"
    );

    ProductManagement::create_product(
        "Ravioles de ricota y nuez o pollo y verdura c/salsa a elección",
        "300",
        "3",
        "3"
    );

    ProductManagement::create_product(
        "Tuco",
        "30",
        "4",
        "3"
    );

    ProductManagement::create_product(
        "Boloñesa",
        "50",
        "4",
        "3"
    );

    ProductManagement::create_product(
        "Salsa blanca",
        "50",
        "4",
        "3"
    );

    ProductManagement::create_product(
        "Pepsi 330ml",
        "60",
        "5",
        "3"
    );

    ProductManagement::create_product(
        "Mirinda 330ml",
        "60",
        "5",
        "3"
    );

    ProductManagement::create_product(
        "Paso de los toros pomelo 330ml",
        "60",
        "5",
        "3"
    );

    ProductManagement::create_product(
        "7up 330ml",
        "60",
        "5",
        "3"
    );

    ProductManagement::create_product(
        "Agua mineral 500ml",
        "65",
        "5",
        "3"
    );

    ProductManagement::create_product(
        "Vacío con guarnición",
        "450",
        "6",
        "3"
    );

    ProductManagement::create_product(
        "Lomo al champignon",
        "580",
        "6",
        "3"
    );

    ProductManagement::create_product(
        "Bife de chorizo con guarnición",
        "600",
        "6",
        "3"
    );

    ProductManagement::create_product(
        "Parrillada para dos personas",
        "800",
        "6",
        "3"
    );

    ProductManagement::create_product(
        "Ensalada Caesar",
        "260",
        "7",
        "3"
    );

    ProductManagement::create_product(
        "Ensalada Criolla",
        "230",
        "7",
        "3"
    );

    ProductManagement::create_product(
        "Ensalada Primavera",
        "260",
        "7",
        "3"
    );

    ProductManagement::create_product(
        "Tarta de jamón y queso (porción)",
        "250",
        "8",
        "3"
    );

    ProductManagement::create_product(
        "Tarta pascualina (porción)",
        "250",
        "8",
        "3"
    );

    ProductManagement::create_product(
        "Tarta de brocoli (porción)",
        "250",
        "8",
        "3"
    );

    ProductManagement::create_product(
        "Tarta de puerro y champignones (porción)",
        "250",
        "8",
        "3"
    );
}

function load_bakery_products() {
    ProductManagement::create_product(
        "Selva negra (porción)",
        "265",
        "9",
        "4"
    );

    ProductManagement::create_product(
        "Lemon pie (porción)",
        "265",
        "9",
        "4"
    );

    ProductManagement::create_product(
        "Ricota y dulce de leche (porción)",
        "250",
        "9",
        "4"
    );

    ProductManagement::create_product(
        "Flan casero",
        "240",
        "10",
        "4"
    );

    ProductManagement::create_product(
        "Ensalada de frutas",
        "200",
        "10",
        "4"
    );
}

function load_brewery_products() {
    ProductManagement::create_product(
        "Blonde",
        "140",
        "11",
        "5"
    );

    ProductManagement::create_product(
        "IPA",
        "140",
        "11",
        "5"
    );

    ProductManagement::create_product(
        "Stout",
        "140",
        "11",
        "5"
    );

    ProductManagement::create_product(
        "Tripel",
        "150",
        "11",
        "5"
    );

    ProductManagement::create_product(
        "Barley wine",
        "160",
        "11",
        "5"
    );

    ProductManagement::create_product(
        "Honey",
        "140",
        "11",
        "5"
    );
}

function load_bar_products() {
    ProductManagement::create_product(
        "Gin tonic",
        "170",
        "12",
        "6"
    );

    ProductManagement::create_product(
        "Aperol spritz",
        "170",
        "12",
        "6"
    );

    ProductManagement::create_product(
        "Fernet con cola",
        "170",
        "12",
        "6"
    );

    ProductManagement::create_product(
        "Destornillador",
        "150",
        "12",
        "6"
    );

    ProductManagement::create_product(
        "Campari con naranja",
        "140",
        "12",
        "6"
    );

    ProductManagement::create_product(
        "Uxmal malbec 1lt",
        "210",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Uxmal cabernet 1lt",
        "210",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Dadá tinto 1lt",
        "180",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Los haroldos chardonnai 1lt",
        "215",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Los haroldos malbec 1lt",
        "215",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Rutini malbec 1lt",
        "710",
        "13",
        "6"
    );

    ProductManagement::create_product(
        "Rutini cabernet 1lt",
        "710",
        "13",
        "6"
    );
}