// If you want to suggest a new language you can use this file as a template.
// To reduce the file size you should remove the comment lines (the ones that start with // )
if(!window.calendar_languages) {
    window.calendar_languages = {};
}
// Here you define the language and Country code. Replace en-US with your own.
// First letters: the language code (lower case). See http://www.loc.gov/standards/iso639-2/php/code_list.php
// Last letters: the Country code (upper case). See http://www.iso.org/iso/home/standards/country_codes/country_names_and_code_elements.htm
window.calendar_languages['ee'] = {
	error_noview:     'Kalender: Šabloon kujul {0} ei ole leitud.',
	error_dateformat: 'Kalender: vale kuupäeva formaat {0}. Peab olema kas  "now" või "yyyy-mm-dd"',
	error_loadurl:    'Kalender: URL ei ole määratud sündmuste laadimiseks.',
	error_where:      'Kalender: vale navigatsioon {0}. Võib ainult "next", "prev" või "today"',
	error_timedevide: 'Kalender: Aja jagamise parameeter peab jagama 60 täisarvuga. Näiteks 10, 15, 30',

	title_year:  '{0}',
	title_month: '{0} {1}',
	title_week:  '{0} aasta nädal {1}',
	title_day:   '{0}, {1} {2} {3}',

	week:        'Nädal {0}',
	all_day:     'Terve päev',
	time:        'Aeg',
	events:      'Sündmused',
	before_time: 'Lõppevad enne aja vööndi',
	after_time:  'Lõppevad peale aja vööndi',

	m0:  'Jaanuar',
	m1:  'Veebruar',
	m2:  'Märts',
	m3:  'Aprill',
	m4:  'Mai',
	m5:  'Juuni',
	m6:  'Juuli',
	m7:  'August',
	m8:  'September',
	m9:  'Oktoober',
	m10: 'November',
	m11: 'Detsember',

	ms0:  'Jan',
	ms1:  'Veeb',
	ms2:  'Mär',
	ms3:  'Apr',
	ms4:  'Mai',
	ms5:  'Juuni',
	ms6:  'Juuli',
	ms7:  'Aug',
	ms8:  'Sept',
	ms9:  'Okt',
	ms10: 'Nov',
	ms11: 'Dets',

	d0: 'Pühapäev',
	d1: 'Esmaspäev',
	d2: 'Teisipäev',
	d3: 'Kolmapäev',
	d4: 'Neljapäev',
	d5: 'Reede',
	d6: 'Laupäev',

	first_day: 1,

	holidays: {
		'01-01':       'Uusaasta',
		'24-02':       'Eesti Vabariigi aastapäev',
		'03-04':       'Suur reede',
		'05-04':       'Ülestõusmispühade 1. püha',
		'01-05':       'Kevadpüha',
		'24-05':       'Nelipühade 1. püha',
		'23-06':       'Võidupüha',
		'24-06':       'Jaanipäev',
		'20-08':       'Taasiseseisvumispäev',
		'24-12':       'Jõululaupäev',
		'25-12':       'Esimene jõulupüha',
		'26-12':       'Teine jõulupüha'
	}
};