$(document).ready(function() {
    var urlEnCours = document.location.href,
        jeu = '',
        idPartie = '',
        xhr = null;
        
    if (urlEnCours.indexOf('/bleach/')>0) {
        jeu = 'bleach';
    }
    if (urlEnCours.indexOf('/quickstrike/')>0) {
        jeu = 'quickstrike';
    }
    if (urlEnCours.indexOf('/saintseiya/')>0) {
        jeu = 'saintseiya';
    }

    if (urlEnCours.indexOf('/partie/')>0) {
        setTimeout(rafraichir_la_partie(), 750);
        $(document).on('click','a',function(){
            if (xhr != null) {
                xhr.abort();
            }
        });
    }

    function rafraichir_la_partie() {
        var tableau = document.location.href;
        tableau = tableau.split('/');
        idPartie = tableau[tableau.length - 1];

        if (xhr != null) {
            xhr.abort();
        }
        xhr = $.ajax({
            url: Routing.generate('jeus_' + jeu + '_partie_timestamp', {
                id: idPartie
            }),
            type: 'POST',
            success: function(retour) {
                if (retour.timestamp>$('#timestamp').val()) {
                    document.location.reload();                    
                } else {
                    setTimeout(rafraichir_la_partie(), 750);
                }
            },
            error: function(d, e, f) {
            }
        });

    }
    
    /*
     * changement du nom du deck
     */
    $(document).on('keypress', '#nom_deck', function(e) {
        if (e.which == 13) {
            var tableau = document.location.href;

            tableau = tableau.split('/');
            var idDeck = tableau[tableau.length - 1];

            $.ajax({
                url: Routing.generate('jeus_' + jeu + '_deck_renommer', {
                    id: idDeck
                }),
                type: 'POST',
                data: {
                    nom: $(this).val()
                },
                success: function(html) {
                    $('#deck').html('');
                    $('#deck').html(html);
                },
                error: function(d, e, f) {
                }
            });
        }
    });

    /*
     * changement du nom du deck
     */
    $(document).on('click', '.valider-deck', function(e) {
        var tableau = document.location.href;

        tableau = tableau.split('/');
        var idDeck = tableau[tableau.length - 1];

        $.ajax({
            url: Routing.generate('jeus_' + jeu + '_deck_valider', {
                id: idDeck
            }),
            type: 'POST',
            success: function(html) {
                $('#deck').html('');
                $('#deck').html(html);
            },
            error: function(d, e, f) {
            }
        });
    });

    /*
     * ajout d'une carte dans un deck
     */
    $(document).on('click', '.carte_classeur', function(e) {
        var url = document.location.href;
        if (url.indexOf('/deck/')>0) {
            tableau = url.split('/');
            var idDeck = tableau[tableau.length - 1];

            $.ajax({
                url: Routing.generate('jeus_' + jeu + '_deck_ajouterCarte', {
                id: idDeck
                }),
                type: 'POST',
                data: {
                    idCarte: $(this).attr('id')
                },
                success: function(html) {
                    $('#deck').html('');
                    $('#deck').html(html);
                },
                error: function(d, e, f) {
                }
            });
        }

    });

    /*
     * ajout d'une carte dans un deck
     */
    $(document).on('click', '.carte_deck', function(e) {
        var url = document.location.href;
        if (url.indexOf('/deck/')>0) {
            tableau = url.split('/');
            var idDeck = tableau[tableau.length - 1];

            $.ajax({
                url: Routing.generate('jeus_' + jeu + '_deck_supprimerCarte', {
                id: idDeck
                }),
                type: 'POST',
                data: {
                    idCarte: $(this).attr('id')
                },
                success: function(html) {
                    $('#deck').html('');
                    $('#deck').html(html);
                },
                error: function(d, e, f) {
                }
            });
        }

    });

    $(document).on('hover', '.carte_classeur', function() {
        $('#aggrandi-'.$(this).val()).style('display:block');        
    });

    $(document).on('click', '#jeus_quickstrikebundle_selecteur_typeCarte input', function(e) {
        var id='';
        $.each('#jeus_quickstrikebundle_selecteur_typeCarte input', function(){
            id = id + '_' + $(this).val();
        });
        alert(id);

        // $.ajax({
        //     url: Routing.generate('jeus_' + jeu + '_deck_valider', {
        //         id: idDeck
        //     }),
        //     type: 'POST',
        //     success: function(html) {
        //         $('#deck').html('');
        //         $('#deck').html(html);
        //     },
        //     error: function(d, e, f) {
        //     }
        // });

    });

});