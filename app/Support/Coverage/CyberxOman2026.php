<?php

namespace App\Support\Coverage;

/**
 * تغطية «خمس محطات» من CyberX Oman 2026 بقلم سالم الحجري -- المحتوى
 * بلغتين (الملخصات، جمل الربط، وجلسة النقاش من resources/data/cyberx-2026).
 */
class CyberxOman2026
{
    public const EVENT_SLUG = 'cyberx-oman-2026';

    public static function content(string $locale): array
    {
        $isEn = $locale === 'en';
        $t = fn (string $ar, string $en) => $isEn ? $en : $ar;

        $cards = [
            [
                'name' => 'يحيى العزري',
                'image' => 'images/events/cyberx-2026/yahya-al-azri.png',
                'ratio' => 445 / 465,
                'headline' => 'الثقة الرقمية تبدأ بالاستعداد للاختراق والقدرة على التعافي',
                'paragraphs' => [
                    'أكد يحيى العزري، خبير تقنية المعلومات والأمن السيبراني في المركز الوطني للسلامة المعلوماتية (Oman CERT)، خلال جلسة «الثقة الرقمية والمرونة السيبرانية» في مؤتمر سايبر إكس عُمان 2026، أن حماية المؤسسات تتطلب الاستعداد للاختراق والقدرة على مواصلة العمل والتعافي منه. واستعرض أمثلة لهجمات طالت قطاعات الصحة والطيران والطاقة والمياه، موضحًا أن تعطل مورد أو شريك تقني قد يؤثر في منظومة كاملة، ولذلك يجب أن تشمل خطط التعافي الموردين والأنظمة المرتبطة بالمؤسسة.',
                    'وأوضح أن الذكاء الاصطناعي يزيد تعقيد التهديدات، من خلال تسريع تطوير البرمجيات الخبيثة، وتغيير خصائصها لتفادي الكشف، واستنساخ الأصوات وتزييف الفيديو، بما يصعّب التحقق من الهوية والمحتوى.',
                    'وطرح إطارًا دفاعيًا يقوم على افتراض وقوع الاختراق، وتطبيق دفاع متعدد الطبقات والثقة الصفرية، مع إبقاء الإنسان ضمن حلقة اتخاذ القرار. ويشمل ذلك كشف التزييف العميق، وتطوير أنظمة ذكاء اصطناعي آمنة، وأتمتة الاستجابة للحوادث.',
                    'وشدد على دور الإدارة العليا في بناء المرونة السيبرانية، وتأهيل الكوادر، وتعزيز الشراكات، وحوكمة استخدام الذكاء الاصطناعي، ونشر الوعي الأمني. كما أشار إلى أهمية التكامل مع الهوية الرقمية الوطنية في عُمان لتعزيز الثقة بالخدمات الرقمية.',
                ],
            ],
            [
                'name' => 'فاطمة اللواتي',
                'lead' => 'افتراض وقوع الاختراق هو البداية، لكن ماذا بعده؟ كيف تواصل المؤسسة عملها وأنظمتها متوقفة؟ هنا تنقلنا فاطمة اللواتي من الالتزام بالمعايير إلى الاستمرارية الحقيقية.',
                'image' => 'images/events/cyberx-2026/fatma-al-lawati.png',
                'ratio' => 1,
                'headline' => 'الامتثال وحده لا يكفي لحماية استمرارية المؤسسات',
                'paragraphs' => [
                    'أكدت فاطمة اللواتي، خلال كلمتها في مؤتمر سايبر إكس عُمان 2026، أن الالتزام بالمعايير والسياسات لا يكفي لضمان قدرة المؤسسات على مواجهة الاضطرابات السيبرانية. فالامتثال يوضح الجاهزية على الورق، بينما تظهر المرونة في قدرة المؤسسة على مواصلة خدماتها الأساسية والتعافي أثناء الأزمات.',
                    'وحددت خمس أولويات لبناء هذه المرونة، تبدأ بتحديد الخدمات الحرجة وما تعتمد عليه من تقنية وبيانات وأشخاص وموردين، وترتيب أولويات استعادتها. وتشمل أيضًا حوكمة واضحة تحدد المسؤوليات وصلاحيات القرار قبل وقوع الحوادث، مع مشاركة الإدارة العليا في فهم المخاطر وإدارة الاستجابة.',
                    'وشددت على اختبار الجاهزية عبر محاكاة واقعية تجمع الفرق التقنية والقيادات، وتشمل سيناريوهات مثل تعطل مزود رئيسي. كما أوضحت أن الاعتماد على السحابة والاتصالات والأطراف الثالثة يجعل التعاون مع الموردين والجهات المعنية جزءًا أساسيًا من استمرارية العمل.',
                    'ودعت إلى التعلم من كل حادث وتحديث خطط الاستجابة والتعافي باستمرار، وإدماج الأمن في تصميم الخدمات منذ البداية. واختتمت بأن حماية الاستمرارية تدعم الثقة في الاقتصاد الرقمي، وأن السؤال الأهم للمؤسسات هو: هل نستطيع الاستمرار عندما تقع الأزمة؟',
                ],
            ],
            [
                'name' => 'رينيل وحيد',
                'lead' => 'وإذا كانت الاستمرارية مهمة في أي مؤسسة، ففي المستشفى قد تصبح مسألة حياة. رينيل وحيد يأخذنا إلى غرفة الطوارئ.',
                'subtitle' => 'SOFPITAL · Health Systems',
                'image' => 'images/events/cyberx-2026/rineel-wahid-v2.png',
                'ratio' => 1,
                'headline' => 'الأمن السيبراني في القطاع الصحي حماية للمرضى واستمرار للرعاية',
                'paragraphs' => [
                    'أكد رينيل وحيد، مدير تقنية المعلومات في SOFPITAL Health Systems SPC، خلال مؤتمر سايبر إكس عُمان 2026، أن الأمن السيبراني في القطاع الصحي يرتبط مباشرة بسلامة المرضى. واستهل كلمته بمشهد لمريض يصل إلى الطوارئ بينما تتعطل الأنظمة، فلا يستطيع الفريق الطبي الوصول إلى سجله أو معرفة أدويته، موضحًا أن الهجمات قد تؤثر في قرارات علاجية عاجلة.',
                    'وأشار إلى أن حساسية البيانات الصحية، وكثرة الأجهزة الطبية المتصلة، والأنظمة القديمة تجعل حماية المؤسسات الصحية أكثر تعقيدًا. وطرح خارطة طريق من خمس مراحل: حصر الأجهزة والأنظمة والموردين، وإتقان أساسيات الحماية، واكتشاف الهجمات واحتواؤها، والاستعداد للتعافي، وبناء ثقافة أمنية لدى الموظفين.',
                    'وشدد على أهمية تقسيم الشبكات للحد من انتشار الهجوم، ووضع إجراءات واضحة للاستجابة تمنع فصل الأجهزة عشوائيًا أثناء الأزمة. كما دعا إلى تدريبات توعوية بالعربية والإنجليزية وتشجيع الإبلاغ عن الرسائل المشبوهة.',
                    'وأوضح أن الذكاء الاصطناعي يمكن استخدامه للدفاع والهجوم، وأن توظيفه في الرعاية الصحية يتطلب حماية بيانات المرضى وضبط مشاركتها. واختتم بأن الهدف من الأمن السيبراني هو حماية الأشخاص الذين وثقوا بالمؤسسة، وضمان استمرار تقديم الرعاية لهم.',
                ],
            ],
            [
                'name' => 'شابيل بشير',
                'lead' => 'حماية المريض تبدأ باكتشاف الهجوم قبل أن يصل إليه. وهنا يأتي دور الذكاء الاصطناعي في مساندة فرق الأمن، مع شابيل بشير من ESET الشرق الأوسط.',
                'subtitle' => 'ESET Middle East',
                'image' => 'images/events/cyberx-2026/shabil-basheer-v2.png',
                'ratio' => 1,
                'headline' => 'الذكاء الاصطناعي لا يستبدل المحلل البشري… بل يساعده على القرار أسرع',
                'paragraphs' => [
                    'أكد شابيل بشير، مهندس ما قبل البيع الرئيسي في ESET الشرق الأوسط، خلال مؤتمر سايبر إكس عُمان 2026، أن المهاجمين باتوا يعملون «بسرعة الآلة» بينما يعمل المدافعون بسرعة البشر، مشيرًا إلى أن أسرع اختراق رُصد استغرق 27 ثانية فقط، وأن كثرة التنبيهات باتت ترهق فرق الأمن.',
                    'وأوضح أن القيمة الحقيقية للذكاء الاصطناعي ليست في اكتشاف تهديدات أكثر، بل في فهمها: الكشف بالسلوك بدل التواقيع المعروفة، وتحليل البرمجيات الخبيثة في السحابة خلال دقائق، وربط عشرات التنبيهات في «قصة هجوم» واحدة، وترتيب الأولويات حسب المخاطر.',
                    'وأشار إلى أن نحو 80% من الشركات تستخدم الذكاء الاصطناعي، ما يوسّع سطح الهجوم، داعيًا إلى حماية محادثات الموظفين مع أدوات الذكاء الاصطناعي ومنع تسرب البيانات الحساسة، ومراقبة سلوك وكلاء الذكاء الاصطناعي.',
                    'واختتم بأن الذكاء الاصطناعي لا يحل محل المحلل البشري، بل يمنحه السياق والمعلومة ليتخذ القرار الصحيح بسرعة أكبر.',
                ],
            ],
        ];

        if ($isEn) {
            $cards = [
                [
                    'name' => 'Yahya Al-Azri',
                    'image' => 'images/events/cyberx-2026/yahya-al-azri.png',
                    'ratio' => 445 / 465,
                    'headline' => 'Digital trust starts with being ready for a breach and able to recover',
                    'paragraphs' => [
                        'Speaking at the «Digital Trust and Cyber Resilience» session at CyberX Oman 2026, Yahya Al-Azri, ICT and Cybersecurity Expert at Oman CERT (MTCIT), stressed that protecting organizations requires being prepared for a breach and able to keep operating and recover from it. He reviewed attacks that hit the healthcare, aviation, energy and water sectors, explaining that the failure of a single supplier or technology partner can affect an entire ecosystem — which is why recovery plans must cover suppliers and every system connected to the organization.',
                        'He explained that AI is making threats more complex: speeding up malware development, changing its characteristics to evade detection, and cloning voices and faking video, which makes verifying identity and content harder.',
                        'He proposed a defensive framework built on assuming breach, layered defense and zero trust, while keeping humans in the decision-making loop. This includes detecting deepfakes, building secure AI systems, and automating incident response.',
                        'He emphasized the role of senior leadership in building cyber resilience, developing talent, strengthening partnerships, governing the use of AI and spreading security awareness. He also pointed to the importance of integrating with Oman\'s national digital identity to strengthen trust in digital services.',
                    ],
                ],
                [
                    'name' => 'Fatma Al Lawati',
                    'lead' => 'Assuming a breach is the starting point — but what comes next? How does an organization keep operating when its systems go down? Here, Fatma Al Lawati takes us from meeting standards to real continuity.',
                    'image' => 'images/events/cyberx-2026/fatma-al-lawati.png',
                    'ratio' => 1,
                    'headline' => 'Compliance alone is not enough to protect business continuity',
                    'paragraphs' => [
                        'In her talk at CyberX Oman 2026, Fatma Al Lawati stressed that adhering to standards and policies is not enough to ensure organizations can withstand cyber disruption. Compliance shows readiness on paper, while resilience shows in an organization\'s ability to keep its essential services running and recover during a crisis.',
                        'She set out five priorities for building that resilience, starting with identifying critical services and the technology, data, people and suppliers they depend on, and prioritizing their recovery. They also include clear governance that defines responsibilities and decision rights before incidents happen, with senior leadership involved in understanding risks and managing the response.',
                        'She stressed testing readiness through realistic simulations that bring technical teams and leaders together, including scenarios such as the outage of a key provider. She also explained that reliance on cloud, telecom and third parties makes cooperation with suppliers and stakeholders a core part of business continuity.',
                        'She called for learning from every incident, continuously updating response and recovery plans, and building security into the design of services from the start. She concluded that protecting continuity supports trust in the digital economy, and that the most important question for any organization is: can we keep going when the crisis hits?',
                    ],
                ],
                [
                    'name' => 'Rineel Wahid',
                    'lead' => 'And if continuity matters in any organization, in a hospital it can be a matter of life and death. Rineel Wahid takes us into the emergency room.',
                    'subtitle' => 'SOFPITAL · Health Systems',
                    'image' => 'images/events/cyberx-2026/rineel-wahid-v2.png',
                    'ratio' => 1,
                    'headline' => 'Cybersecurity in healthcare protects patients and keeps care going',
                    'paragraphs' => [
                        'Speaking at CyberX Oman 2026, Rineel Wahid, IT Director at SOFPITAL Health Systems SPC, stressed that cybersecurity in the healthcare sector is directly tied to patient safety. He opened with a scene of a patient arriving at the emergency room while the systems are down, leaving the medical team unable to access the patient\'s record or know their medications — explaining that attacks can disrupt urgent treatment decisions.',
                        'He noted that the sensitivity of health data, the large number of connected medical devices and legacy systems make protecting healthcare organizations more complex. He proposed a five-stage roadmap: inventorying devices, systems and suppliers; mastering the security basics; detecting and containing attacks; preparing for recovery; and building a security culture among staff.',
                        'He stressed the importance of network segmentation to limit the spread of an attack, and clear response procedures that prevent devices from being disconnected at random during a crisis. He also called for awareness training in both Arabic and English, and for encouraging staff to report suspicious messages.',
                        'He explained that AI can be used for both defense and attack, and that using it in healthcare requires protecting patient data and controlling how it is shared. He concluded that the purpose of cybersecurity is to protect the people who placed their trust in the organization, and to make sure they keep receiving care.',
                    ],
                ],
                [
                    'name' => 'Shabil Basheer',
                    'lead' => 'Protecting the patient also means detecting threats before they disrupt care. This is where AI comes in to support security teams, with Shabil Basheer of ESET Middle East.',
                    'subtitle' => 'ESET Middle East',
                    'image' => 'images/events/cyberx-2026/shabil-basheer-v2.png',
                    'ratio' => 1,
                    'headline' => 'AI doesn\'t replace the human analyst… it helps them decide faster',
                    'paragraphs' => [
                        'Speaking at CyberX Oman 2026, Shabil Basheer, Lead Presales Engineer at ESET Middle East, said attackers now operate «at machine speed» while defenders work at human speed, noting that the fastest breach on record took just 27 seconds and that alert overload is exhausting security teams.',
                        'He explained that AI\'s real value lies not in detecting more threats but in understanding them: behavior-based detection instead of known signatures, cloud malware analysis within minutes, linking dozens of alerts into a single «attack story», and prioritizing by risk.',
                        'He noted that around 80% of companies use AI, which widens the attack surface, and called for protecting employees\' conversations with AI tools, preventing sensitive data leaks, and monitoring the behavior of AI agents.',
                        'He concluded that AI does not replace the human analyst; it gives them the context and information to make the right decision faster.',
                    ],
                ],
            ];
        }

        $roles = $isEn
            ? ['ICT and Cybersecurity Expert · Oman CERT, MTCIT', 'Cybersecurity Executive, Speakers Lead · Women in Cybersecurity Middle East (WiCSME)', 'IT Director · SOFPITAL Health Systems', 'Lead Presales Engineer · ESET Middle East']
            : ['خبير تقنية المعلومات والأمن السيبراني · المركز الوطني للسلامة المعلوماتية (Oman CERT)', 'قيادية في الأمن السيبراني، مسؤولة المتحدثين · النساء في الأمن السيبراني بالشرق الأوسط (WiCSME)', 'مدير تقنية المعلومات · SOFPITAL', 'مهندس ما قبل البيع الرئيسي · ESET الشرق الأوسط'];

        $stops = [];
        foreach ($cards as $i => $card) {
            $stops[] = [
                'lead' => $card['lead'] ?? null,
                'name' => $card['name'],
                'role' => $roles[$i] ?? '',
                'sub' => null,
                'image' => asset($card['image']),
                'group' => false,
                'headline' => $card['headline'],
                'blocks' => array_map(fn ($x) => ['t' => 'p', 'x' => $x], $card['paragraphs']),
            ];
        }

        // المحطة الخامسة: جلسة النقاش. أول كتلتين بالملف (عنوان الجلسة الكامل
        // والعنوان الرئيسي) تطلع فوق المقال، فما نكررها داخله.
        $panel = json_decode(file_get_contents(resource_path('data/cyberx-2026/panel.'.($isEn ? 'en' : 'ar').'.json')), true);

        $stops[] = [
            'lead' => $t('لكن الذكاء الاصطناعي نفسه يحتاج إلى من يضبطه: ما البيانات التي يصل إليها؟ ومن يتحمّل القرار؟ هذا ما ناقشته الحلقة الختامية.', 'But AI itself needs someone to keep it in check: what data can it reach? And who remains accountable for the decision? That\'s what the closing panel discussed.'),
            'name' => $t('جلسة نقاش', 'Panel Discussion'),
            'role' => $t('تأمين مؤسسات الذكاء الاصطناعي', 'Securing the AI Enterprise'),
            'sub' => $panel[0]['x'],
            'image' => asset('images/events/cyberx-2026/panel-group.png'),
            'group' => true,
            'headline' => $panel[1]['x'],
            'blocks' => array_slice($panel, 2),
        ];

        return [
            'host' => [
                'name' => $t('سالم الحجري', 'Salem Al Hajri'),
                'role' => $t('منصة الياسي · من قلب المؤتمر', 'ALYASI · From CyberX Oman 2026'),
                'avatar' => asset('images/events/cyberx-2026/salem-al-hajri.png'),
                'intro' => $t('من سايبر إكس عُمان 2026 في مسقط، أنقل لكم خمس محطات توقفت عندها. ونبدأ بالسؤال الأصعب: ماذا لو وقع الاختراق فعلًا؟', 'From CyberX Oman 2026 in Muscat, here are five moments that stood out to me. Let\'s start with the hardest question: what if a breach actually happens?'),
            ],
            'stops' => $stops,
            'outro' => $t('خمس محطات، ورسالة واحدة: الاستعداد واختبار الجاهزية وحماية البيانات هي ما يُبقي المؤسسات قائمة عندما تتعطل الأنظمة. هذه كانت خمس محطات توقفت عندها في سايبر إكس عُمان 2026.', 'Five stops, one message: cyber resilience starts long before an incident happens. Preparation, readiness testing, data protection and clear accountability are what keep organizations moving when systems fail. These were the five moments that stood out to me at CyberX Oman 2026.'),
            'hall' => asset('images/events/cyberx-2026/coverage-hero.jpg'),
            // التقرير الصوتي للتغطية -- زر «استمع» مكان الفيديو.
            'audio' => [
                'src' => asset('audio/coverage/cyberx-oman-2026/report.m4a'),
                'duration' => 404,
                'label' => $t('التقرير الصوتي', 'Audio report'),
                'title' => $t('استمع لتغطية سايبر إكس عُمان 2026', 'Listen to the CyberX Oman 2026 coverage'),
                'note' => $t('بصوت سالم الحجري', 'Narrated in Arabic by Salem Al Hajri'),
                // متى يبدأ كل جزء بالتسجيل (ثواني): المحطات الخمس، ثم الخلاصة، ثم الشكر --
                // الصفحة تنزل لكل جزء وقت ما يوصله الصوت.
                'cues' => [31.1, 85.0, 140.9, 219.2, 279.7, 349.8, 376.4],
            ],
            // فيديو ملخص التغطية على قناة اليوتيوب.
            'video' => [
                // مخفي مؤقتًا بطلب سالم -- غيّرها لـ true لإرجاعه.
                'visible' => false,
                'id' => 'ATZqGX2DJR4',
                'label' => $t('ملخص التغطية بالفيديو', 'Video summary'),
                'title' => $t('ماذا يحدث عندما يقع الاختراق؟', 'What happens when a breach occurs?'),
                'url' => 'https://youtu.be/ATZqGX2DJR4',
            ],
            'thanks' => [
                'title' => $t('شكر وتقدير', 'With gratitude'),
                'teaser' => $t('اللقاءات الصحفية مع الضيوف · استمع الآن', 'Interviews with the guests · Listen now'),
                // معرض صور الشكر (حتى 4 صور) -- التصميم يتكيّف مع عددها.
                'photos' => [
                    ['src' => asset('images/cyberx-2026/thanks-1.jpg'), 'width' => 1086, 'height' => 1448],
                    ['src' => asset('images/cyberx-2026/thanks-2.jpg'), 'width' => 1086, 'height' => 1448],
                    ['src' => asset('images/cyberx-2026/thanks-3.jpg'), 'width' => 1086, 'height' => 1448],
                    ['src' => asset('images/cyberx-2026/thanks-4.jpg'), 'width' => 1086, 'height' => 1448],
                ],
                'text' => $t(
                    'كل الشكر والتقدير للدكتور هيثم الحجري، والأستاذ علي اللواتي، والأستاذ خالد العمراني، والأستاذ يحيى العزري، على تخصيص جزء من وقتهم لي، وحسن استقبالهم ومشاركتهم القيّمة التي أثرت تجربتي في تغطية مؤتمر سايبر إكس عُمان 2026.',
                    'Heartfelt thanks to Dr. Haitham Al Hajri, Mr. Ali Al-Lawati, Mr. Khalid Al Amrani and Mr. Yahya Al-Azri for giving me some of their time, for their warm welcome, and for their valuable contributions that enriched my experience covering CyberX Oman 2026.'
                ),
            ],
        ];
    }
}
