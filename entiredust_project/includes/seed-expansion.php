<?php
declare(strict_types=1);
require_once __DIR__.'/content-builder.php';

/*
 * One-time content expansion: new categories, ~38 new long-form articles and
 * 3 "hub" pages. Runs once (guarded by a settings flag) and needs no manual
 * SQL import - it uses the same $pdo the rest of the site already has.
 */
function seedInforovaExpansion(PDO $pdo): void
{
    if (setting('content_expansion_v1', '') === 'done') {
        return;
    }

    // ---------------------------------------------------------------
    // Categories (name, description, icon). Existing categories are
    // never overwritten - only a missing icon is filled in.
    // ---------------------------------------------------------------
    $categories = [
        'sarkari-job'       => ['Sarkari Job', 'Government job updates, notices and application guides.', '🏛️'],
        'exam-news'         => ['Exam News', 'Important exam announcements, dates and results.', '📰'],
        'exam-prep'         => ['Exam Prep', 'Study resources, preparation tips and useful explainers.', '📝'],
        'results'           => ['Results', 'Exam results, merit lists and result-checking guides.', '🏆'],
        'scholarships'      => ['Scholarships', 'Scholarship information, application tips and education resources.', '🎓'],
        'career-guide'      => ['Career Guide', 'Practical career guidance, skills and application tips.', '💼'],
        'study-tips'        => ['Study Tips', 'Practical study methods, focus techniques and daily routines.', '📚'],
        'skill-development' => ['Skill Development', 'Digital, communication and workplace skills for today\'s job market.', '🛠️'],
        'current-affairs'   => ['Current Affairs', 'How to follow, note and revise current affairs for exams.', '🌍'],
    ];

    $insCat = $pdo->prepare('INSERT IGNORE INTO categories(name,slug,description) VALUES(?,?,?)');
    $iconCat = $pdo->prepare("UPDATE categories SET icon=? WHERE slug=? AND (icon IS NULL OR icon='')");

    foreach ($categories as $slug => [$name, $desc, $icon]) {
        $insCat->execute([$name, $slug, $desc]);
        $iconCat->execute([$icon, $slug]);
    }

    $catId = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $catId[$row['slug']] = (int) $row['id'];
    }

    // ---------------------------------------------------------------
    // Articles
    // ---------------------------------------------------------------
    $articles = [];

    // ---- Sarkari Job ----------------------------------------------------
    $articles[] = [
        'category' => 'sarkari-job', 'image_topic' => 'office', 'image_index' => 0,
        'title' => "Top Mistakes Candidates Make in Government Job Applications",
        'slug' => "common-mistakes-government-job-applications",
        'excerpt' => "The small errors that get otherwise-eligible applications rejected, and how to avoid them.",
        'intro' => "Every year, applications that would otherwise have been accepted are rejected because of small, avoidable errors. Most of these mistakes happen not because a candidate lacks ability, but because the official notice was read too quickly.",
        'quick_facts' => ["Most rejections happen due to document or format errors, not eligibility", "Read the full notice before filling any form", "Keep scanned documents ready in the exact size and format asked for", "Note the exact application closing time, not just the date"],
        'sections' => [
            ['heading' => "Reading the Notice Too Quickly", 'body' => [
                "A government job notice usually contains eligibility criteria, age limits, required documents, the application process and important dates, all in one place. Skimming it can mean missing a specific instruction, such as a particular certificate format or a category-based relaxation rule.",
                "Before filling any form, read the notice twice: once for a general understanding, and a second time specifically to list every document and detail it asks for.",
            ]],
            ['heading' => "Document and Format Errors", 'body' => [
                "Online application portals are often strict about file size, dimensions and format for photographs and signatures. A file that is even slightly outside the stated limit can cause the form to fail at the final step.",
            ], 'list' => [
                "Photograph and signature in the exact size and format requested",
                "Certificates scanned clearly and named as instructed, if a naming convention is given",
                "Category or reservation certificates from the correct issuing authority",
            ]],
            ['heading' => "Submitting Too Close to the Deadline", 'body' => [
                "Application portals frequently experience heavy traffic in the final hours before a deadline, which can lead to slow loading, payment failures or an inability to submit at all. Submitting a few days early avoids this entirely.",
            ]],
        ],
        'faqs' => [
            ['q' => "Can a rejected application be resubmitted?", 'a' => "This depends entirely on the recruiting authority's own rules, stated in the official notice. Some allow correction windows, others do not."],
            ['q' => "What if I do not have a certificate ready in time?", 'a' => "Start collecting documents as soon as a notice is published, since some certificates take time to issue from the relevant office."],
            ['q' => "Is it safe to pay the application fee through any website?", 'a' => "Only use the official payment gateway linked directly from the recruiting authority's own website."],
        ],
        'conclusion' => "Most application rejections are preventable. A careful first read of the notice, documents prepared well in advance and an early submission remove almost all of the common risks.",
    ];

    $articles[] = [
        'category' => 'sarkari-job', 'image_topic' => 'office', 'image_index' => 1,
        'title' => "Government Job Categories and Pay Scale Basics Explained",
        'slug' => "government-job-categories-pay-scale-basics",
        'excerpt' => "A plain-language explanation of how government job groups and pay structures generally work.",
        'intro' => "Government job notices often refer to a post's group, grade or pay level. These terms can look confusing at first, but they follow a fairly consistent logic once explained simply.",
        'quick_facts' => ["Posts are usually grouped by the type of responsibility and required qualification", "A pay level or grade generally reflects seniority and role complexity", "Allowances are often listed separately from the basic pay figure", "Always confirm exact figures from the official notice, not a summary"],
        'sections' => [
            ['heading' => "How Posts Are Typically Grouped", 'body' => [
                "Government recruitment is commonly organized into broad groups based on the nature of the role, such as technical, administrative or clerical work, and the minimum qualification required to apply.",
                "A higher group generally corresponds to greater responsibility and, in most systems, a higher starting pay level, though this varies by country and department.",
            ]],
            ['heading' => "Understanding Pay Levels and Grades", 'body' => [
                "A pay level or grade pay figure is a reference point used to calculate the actual salary, which usually also includes allowances such as housing or travel support. The number shown in a notice is rarely the full monthly take-home amount.",
            ], 'list' => [
                "Basic pay: the core figure tied to the post's level",
                "Allowances: added on top, and can vary by posting location",
                "Deductions: taxes and contributions are subtracted before the final amount",
            ]],
            ['heading' => "Why the Same Post Can Show Different Figures", 'body' => [
                "It is common to see slightly different salary figures for what appears to be the same post, because allowances differ by city or region, and because notices sometimes show only the basic pay while others show an estimated gross total.",
            ]],
        ],
        'faqs' => [
            ['q' => "Does a higher pay level always mean more responsibility?", 'a' => "In most structured systems yes, though the exact relationship depends on the specific department and country's own rules."],
            ['q' => "Where can I find the exact, final salary for a post?", 'a' => "The official notice or the recruiting department's own salary structure document is the most reliable source."],
            ['q' => "Are allowances the same in every city?", 'a' => "No, allowances such as housing support commonly vary based on the posting location's cost of living classification."],
        ],
        'conclusion' => "Pay-related terms in government notices follow a consistent structure once the basic vocabulary is understood. For exact figures, always refer back to the specific notice rather than a general guide.",
    ];

    $articles[] = [
        'category' => 'sarkari-job', 'image_topic' => 'office', 'image_index' => 2,
        'title' => "How to Prepare a Strong Resume for Government Job Applications",
        'slug' => "resume-for-government-job-applications",
        'excerpt' => "What government application forms and interviews actually look for in a candidate's background summary.",
        'intro' => "Many government application processes ask for a structured summary of education, experience and personal details, either as a formal resume or as fields within an online form. Presenting this clearly makes verification easier and reduces the chance of a mismatch later.",
        'quick_facts' => ["Keep dates, marks and institution names consistent with your certificates", "List qualifications in the order the form or notice requests", "Avoid unnecessary personal opinions or unrelated details", "Double-check spelling of names exactly as they appear on ID documents"],
        'sections' => [
            ['heading' => "Matching the Form to Your Documents", 'body' => [
                "The single most important rule is consistency: names, dates of birth, marks and institution names entered into a form should exactly match what appears on the supporting certificates, since mismatches can cause delays during document verification.",
            ]],
            ['heading' => "What to Include and What to Leave Out", 'body' => [
                "A government application resume is usually factual rather than promotional: educational qualifications in order, relevant work or internship experience, and any specific certifications the post asks for.",
            ], 'list' => [
                "Full name exactly as on your identity document",
                "Educational qualifications with year and percentage or grade",
                "Relevant experience, if the post requires it",
                "Contact details that you check regularly",
            ]],
            ['heading' => "Preparing for a Possible Interview or Verification Round", 'body' => [
                "Some recruitment processes include a document verification stage or an interview. Keeping original documents organized, and being able to explain any gaps in education or employment honestly and briefly, avoids unnecessary stress on the day.",
            ]],
        ],
        'faqs' => [
            ['q' => "Should I include a photo on my resume?", 'a' => "Only if the specific application asks for one; otherwise follow the form's own instructions rather than a general template."],
            ['q' => "What if my name is spelled differently across documents?", 'a' => "This should be corrected or clarified with the issuing authority before applying, since it can cause verification issues later."],
            ['q' => "Is work experience always required?", 'a' => "No, many entry-level government posts do not require prior work experience; check the specific notice."],
        ],
        'conclusion' => "A government job resume does not need to be elaborate. Accuracy, consistency with your documents and a clear structure matter far more than design.",
    ];

    $articles[] = [
        'category' => 'sarkari-job', 'image_topic' => 'office', 'image_index' => 3,
        'title' => "Document Checklist Before You Apply for a Government Job",
        'slug' => "document-checklist-government-job-application",
        'excerpt' => "A practical list of documents worth preparing in advance, before an application window opens.",
        'intro' => "Having documents ready before an application form opens removes most of the last-minute pressure that leads to mistakes. This checklist covers the categories of documents that are commonly required, though the exact list always comes from the official notice.",
        'quick_facts' => ["Start collecting documents as soon as a notice is expected", "Keep both physical and scanned digital copies", "Check the required file size and format for each upload", "Store everything in one labeled folder, physical and digital"],
        'sections' => [
            ['heading' => "Identity and Personal Documents", 'body' => [
                "These typically include a government-issued identity document, proof of date of birth, and a recent passport-style photograph in the exact format the portal requests.",
            ], 'list' => [
                "Identity proof (as accepted by the recruiting authority)",
                "Birth certificate or equivalent date-of-birth proof",
                "Recent photograph in the specified size",
                "Signature scan on plain paper, as specified",
            ]],
            ['heading' => "Educational and Category Certificates", 'body' => [
                "Mark sheets and certificates for every qualification listed on the form, and, where applicable, category, disability or domicile certificates issued by the correct authority, are commonly required at the verification stage even if not uploaded initially.",
            ]],
            ['heading' => "Keeping Everything Organized", 'body' => [
                "A simple folder system, both on a device and in physical form, saves significant time. Naming scanned files clearly, and keeping a printed set ready for in-person verification, prevents scrambling when a deadline is close.",
            ]],
        ],
        'faqs' => [
            ['q' => "Do I need to attest photocopies of documents?", 'a' => "Some recruitment processes require attested copies at the verification stage; check the specific notice for this requirement."],
            ['q' => "What if a certificate is still being processed?", 'a' => "Contact the issuing institution as early as possible, since processing times can vary significantly."],
            ['q' => "Should scanned copies be in color or black and white?", 'a' => "Follow the exact instruction in the notice or portal; requirements differ between recruiting authorities."],
        ],
        'conclusion' => "A short amount of preparation before an application opens can save considerable stress later. Keeping a standard set of documents ready is one of the simplest ways to stay prepared for any notice.",
    ];

    // ---- Exam News --------------------------------------------------------
    $articles[] = [
        'category' => 'exam-news', 'image_topic' => 'news', 'image_index' => 0,
        'title' => "Understanding Exam Admit Cards and Common Issues",
        'slug' => "exam-admit-card-common-issues",
        'excerpt' => "What an admit card usually contains, and what to do when the details look wrong.",
        'intro' => "An admit card is the document that confirms a candidate's exam details: date, time, venue and roll number. Small errors on it can cause real problems on exam day, so checking it carefully as soon as it is released matters.",
        'quick_facts' => ["Download the admit card as soon as it is released, do not wait", "Check name, photo, exam center and timing immediately", "Keep both a printed and digital copy for exam day", "Contact the exam authority early if any detail looks wrong"],
        'sections' => [
            ['heading' => "What to Check First", 'body' => [
                "As soon as an admit card is downloaded, compare every detail against the application form: name spelling, date of birth, photograph, exam center address and the reporting time.",
            ], 'list' => [
                "Name and photograph match your identity document",
                "Exam date, reporting time and venue address",
                "Roll number and any category or code fields",
            ]],
            ['heading' => "Common Issues and What They Usually Mean", 'body' => [
                "A blurred photograph, a swapped exam center, or a name spelled differently from the identity document are the most frequently reported issues. Most exam authorities publish a specific correction window or a helpdesk contact for exactly this situation.",
            ]],
            ['heading' => "What to Do on Exam Day", 'body' => [
                "Carry both a printed admit card and the identity document it was generated with. Arriving well before the reporting time avoids the stress of last-minute security or verification queues.",
            ]],
        ],
        'faqs' => [
            ['q' => "What if the admit card is not generated by the expected date?", 'a' => "Check the official exam authority website for any announced delay before assuming there is a personal issue with your application."],
            ['q' => "Can the exam center be changed after the admit card is released?", 'a' => "This is uncommon and depends entirely on the exam authority's own policy; contact their official helpdesk directly."],
            ['q' => "Is a photocopy of the admit card acceptable?", 'a' => "Most exam centers require the original printed admit card along with a valid identity document."],
        ],
        'conclusion' => "The admit card is a small document with a large amount of important information packed into it. A careful check as soon as it is released is one of the easiest ways to avoid exam-day problems.",
    ];

    $articles[] = [
        'category' => 'exam-news', 'image_topic' => 'news', 'image_index' => 1,
        'title' => "How to Track Exam Results the Right Way",
        'slug' => "how-to-track-exam-results",
        'excerpt' => "Reliable habits for finding a genuine result announcement without falling for unofficial sources.",
        'intro' => "Result day often brings a flood of unofficial posts and forwarded messages, some accurate and some not. Knowing where to check first, and what to ignore, keeps the process simple and stress-free.",
        'quick_facts' => ["Bookmark the official exam authority website in advance", "Ignore result claims shared only through social media forwards", "A genuine result page usually asks for a roll number, not a password", "Save or screenshot the result page once it appears"],
        'sections' => [
            ['heading' => "Where to Check First", 'body' => [
                "The exam authority's own official website, or a result portal explicitly linked from it, is the only fully reliable source. Bookmarking this address in advance, right after the exam, avoids searching under time pressure on result day.",
            ]],
            ['heading' => "Recognizing an Unofficial or Fake Result Page", 'body' => [
                "Pages that ask for a password, payment, or personal information beyond a roll number and date of birth are not typical of genuine result portals and should be treated with caution.",
            ], 'list' => [
                "Check the website address carefully before entering any details",
                "A real result page rarely asks for a payment to view a result",
                "When unsure, navigate from the exam authority's known official homepage instead of a shared link",
            ]],
            ['heading' => "After the Result Is Out", 'body' => [
                "Save a copy of the result page or download the official scorecard if one is offered, since portals are not always available indefinitely after the announcement.",
            ]],
        ],
        'faqs' => [
            ['q' => "Why do some websites show results before the official announcement?", 'a' => "These are almost always unreliable or fake; the official result time is set by the exam authority alone."],
            ['q' => "What if the official website is slow on result day?", 'a' => "High traffic is common right after an announcement; trying again after a short wait is usually enough."],
            ['q' => "Should I trust a result shared in a group chat?", 'a' => "Always verify it independently on the official website before treating it as confirmed."],
        ],
        'conclusion' => "Checking results directly from the official source, rather than through forwarded links, is the simplest way to avoid both misinformation and potential scams on result day.",
    ];

    $articles[] = [
        'category' => 'exam-news', 'image_topic' => 'news', 'image_index' => 2,
        'title' => "What to Do If You Miss an Exam Registration Deadline",
        'slug' => "missed-exam-registration-deadline",
        'excerpt' => "Practical steps to take, and what generally is and is not possible, after a deadline passes.",
        'intro' => "Missing a registration deadline is stressful, but the options that remain depend heavily on the specific exam authority's own rules. Understanding the usual patterns helps in deciding what to do next.",
        'quick_facts' => ["Some exam authorities offer a late registration window with an extra fee", "Check the official notice or website before assuming nothing can be done", "Set independent reminders for future exam cycles", "A missed deadline for one exam does not affect eligibility for others"],
        'sections' => [
            ['heading' => "Check for a Late Registration Window", 'body' => [
                "Many exam authorities, though not all, offer a short extended registration period after the original deadline, usually with an additional late fee. This is announced separately, so checking the official website directly is worthwhile before assuming the opportunity is lost.",
            ]],
            ['heading' => "If No Extension Is Available", 'body' => [
                "When there is genuinely no late window, the only realistic option is usually to prepare for the next exam cycle. Using the extra time to strengthen weak areas can turn a missed deadline into a better-prepared future attempt.",
            ]],
            ['heading' => "Preventing This in the Future", 'body' => [
                "Setting an independent reminder, separate from any single source, a few days before a deadline is one of the most effective ways to avoid missing future registration windows.",
            ], 'list' => [
                "Set a calendar reminder several days before the actual deadline",
                "Follow the official exam authority's own announcement channel",
                "Keep a personal list of exam cycles relevant to your goals",
            ]],
        ],
        'faqs' => [
            ['q' => "Can I request an exception after a deadline has passed?", 'a' => "This is rarely granted and depends entirely on the specific exam authority's own policy."],
            ['q' => "Does missing one exam registration affect future eligibility?", 'a' => "Generally no, as long as you still meet the standard eligibility criteria for the next cycle."],
            ['q' => "Is a late fee refundable if the extended window is also missed?", 'a' => "This depends on the specific exam authority; check their refund policy directly."],
        ],
        'conclusion' => "A missed deadline is rarely the end of an opportunity. Checking for a late window, and building better reminder habits, keeps future exam cycles on track.",
    ];

    $articles[] = [
        'category' => 'exam-news', 'image_topic' => 'news', 'image_index' => 3,
        'title' => "A Simple Guide to Answer Key Objections and Re-evaluation",
        'slug' => "answer-key-objections-re-evaluation-guide",
        'excerpt' => "How provisional answer keys, objection windows and re-evaluation requests generally work.",
        'intro' => "After many exams, a provisional answer key is published before the final result, giving candidates a chance to raise objections on specific questions. Understanding this process helps candidates use it effectively rather than missing the window.",
        'quick_facts' => ["A provisional answer key is usually not final", "Objections are typically accepted only within a short, fixed window", "Evidence or a reference source is often required to support an objection", "A final answer key is normally published after objections are reviewed"],
        'sections' => [
            ['heading' => "What a Provisional Answer Key Is", 'body' => [
                "This is a first version of the expected correct answers, released before the final result, specifically so that candidates can point out questions they believe are incorrect, ambiguous or have more than one valid answer.",
            ]],
            ['heading' => "How to Raise an Objection Properly", 'body' => [
                "Most exam authorities require objections to be submitted through an official portal, sometimes with a small fee per question and supporting reference material, such as a textbook citation, within a clearly stated deadline.",
            ], 'list' => [
                "Identify the exact question number and the answer you are objecting to",
                "Provide a credible reference source where possible",
                "Submit within the official window, since late objections are usually not accepted",
            ]],
            ['heading' => "What Happens After Objections Are Reviewed", 'body' => [
                "A subject expert panel typically reviews all objections before a final answer key is released. This final key is used to calculate the actual result, which is why it can sometimes differ slightly from the provisional one.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is there always a fee to raise an objection?", 'a' => "This varies by exam authority; some charge a small per-question fee, others do not."],
            ['q' => "Will I be told if my specific objection was accepted?", 'a' => "This depends on the exam authority; many only publish the final key without individual responses."],
            ['q' => "Can the final answer key still be challenged after release?", 'a' => "Usually not through the same objection process; check the specific exam authority's rules for any further options."],
        ],
        'conclusion' => "The answer key objection window is a genuine opportunity to correct errors, but only within its stated deadline. Acting quickly and providing solid supporting evidence gives an objection the best chance of being considered.",
    ];

    // ---- Exam Prep ----------------------------------------------------
    $articles[] = [
        'category' => 'exam-prep', 'image_topic' => 'study', 'image_index' => 0,
        'title' => "Active Recall vs Passive Reading for Exam Preparation",
        'slug' => "active-recall-vs-passive-reading",
        'excerpt' => "Why testing yourself works better than re-reading, and how to build the habit.",
        'intro' => "Re-reading a chapter feels productive because the material seems familiar, but research on learning consistently shows that actively recalling information, without looking at the notes, builds much stronger long-term memory.",
        'quick_facts' => ["Recognizing information is not the same as being able to recall it", "Active recall means testing yourself, not re-reading notes", "Spacing recall sessions over days works better than one long session", "Wrong answers during recall are valuable, they show exactly what to review"],
        'sections' => [
            ['heading' => "Why Re-Reading Feels Easier but Works Less Well", 'body' => [
                "When re-reading a chapter, the brain recognizes the familiar text and mistakes that recognition for genuine understanding. This is often called the illusion of competence, and it is the main reason students who re-read heavily can still struggle on exam day.",
            ]],
            ['heading' => "How to Practice Active Recall", 'body' => [
                "After reading a section once, close the book and try to write down or say out loud everything you remember, then check what was missed. This single habit, repeated consistently, is one of the most effective study techniques available.",
            ], 'list' => [
                "Read a section once for understanding",
                "Close the material and recall it from memory",
                "Check against the original and note gaps",
                "Revisit the gaps a day or two later",
            ]],
            ['heading' => "Combining Recall With Spaced Repetition", 'body' => [
                "Recalling information once is useful, but recalling it again after a gap of a day, then a few days, then a week, is what moves it into long-term memory rather than short-term familiarity.",
            ]],
        ],
        'faqs' => [
            ['q' => "Does active recall work for all subjects?", 'a' => "Yes, though the format changes: flashcards for facts, practice problems for numerical subjects, and self-explanation for concepts."],
            ['q' => "How long should a recall session be?", 'a' => "Short, focused sessions of 20 to 30 minutes are usually more effective than long unbroken sessions."],
            ['q' => "Is it normal to forget a lot during early recall attempts?", 'a' => "Yes, this is expected and is actually part of how the technique strengthens memory over repeated attempts."],
        ],
        'conclusion' => "Active recall requires more effort than re-reading, which is exactly why it works better: the effort of retrieving information is what builds durable memory for exam day.",
    ];

    $articles[] = [
        'category' => 'exam-prep', 'image_topic' => 'study', 'image_index' => 1,
        'title' => "How to Build a Distraction-Free Study Space at Home",
        'slug' => "distraction-free-study-space-at-home",
        'excerpt' => "Practical, low-cost changes to a study area that noticeably improve focus.",
        'intro' => "A study space does not need to be elaborate, but a few consistent, low-cost changes can significantly reduce the number of times focus breaks during a session.",
        'quick_facts' => ["A dedicated spot, even a small one, works better than studying anywhere", "Phone notifications are one of the biggest sources of lost focus", "Good lighting reduces fatigue during long sessions", "A consistent spot helps the brain associate it with focus over time"],
        'sections' => [
            ['heading' => "Choosing and Setting Up the Spot", 'body' => [
                "A single, consistent location, even a corner of a shared room, works better than studying in a different place every day, because the brain gradually associates that specific spot with focused work.",
            ], 'list' => [
                "Good lighting, ideally natural light where possible",
                "A clear surface with only current study materials on it",
                "A comfortable but not overly relaxing chair",
            ]],
            ['heading' => "Managing Phone and Digital Distractions", 'body' => [
                "Keeping a phone in another room, or using a simple do-not-disturb mode during study blocks, removes the single biggest source of interruption for most students. Even silent notifications pull attention away from the current task.",
            ]],
            ['heading' => "Handling Noise and Household Interruptions", 'body' => [
                "Where a fully quiet space is not possible, simple headphones or a consistent low background sound can help mask irregular noise. Agreeing on quiet study hours with family members at home also reduces interruptions significantly.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is background music helpful or harmful while studying?", 'a' => "This varies by person and task; instrumental or low-lyric music works for some, while others focus best in silence."],
            ['q' => "What if I do not have a separate room to study in?", 'a' => "A consistent corner or desk, used only for study during set hours, can work nearly as well as a separate room."],
            ['q' => "How often should the study space be reorganized?", 'a' => "Keeping it consistently tidy matters more than frequent reorganizing; clutter is the main distraction to avoid."],
        ],
        'conclusion' => "Small, consistent changes to a study space, rather than an expensive setup, are usually enough to noticeably improve focus over a few weeks.",
    ];

    $articles[] = [
        'category' => 'exam-prep', 'image_topic' => 'study', 'image_index' => 2,
        'title' => "Time Management Techniques for Competitive Exam Preparation",
        'slug' => "time-management-competitive-exam-preparation",
        'excerpt' => "How to divide limited preparation time across subjects without feeling overwhelmed.",
        'intro' => "Competitive exam preparation usually involves multiple subjects competing for the same limited hours. A simple, realistic time structure prevents both burnout and the common mistake of over-preparing one subject while neglecting others.",
        'quick_facts' => ["List all subjects and rate your comfort level with each honestly", "Weaker subjects generally need more, not less, scheduled time", "Build in fixed revision time, not just new topics", "A realistic plan followed consistently beats an ideal plan followed rarely"],
        'sections' => [
            ['heading' => "Auditing Where Your Time Actually Goes", 'body' => [
                "Before building a new schedule, tracking a few days of actual study time honestly reveals where time is genuinely spent, which is often quite different from where a student assumes it goes.",
            ]],
            ['heading' => "Building a Weekly, Not Just Daily, Plan", 'body' => [
                "A weekly view makes it easier to balance subjects fairly: strong subjects can be maintained with shorter sessions, while weaker ones are given more frequent, focused blocks across the week rather than one long cram session.",
            ], 'list' => [
                "List every subject and a rough weekly time target",
                "Give weaker subjects more frequent shorter sessions",
                "Reserve fixed weekly slots for revision and practice tests",
            ]],
            ['heading' => "Protecting Time for Practice Tests", 'body' => [
                "It is common to spend almost all available time on learning new material and very little on timed practice. Since most competitive exams are also a test of speed and accuracy under pressure, practice tests deserve a fixed, protected slot every week.",
            ]],
        ],
        'faqs' => [
            ['q' => "How many hours a day is ideal for exam preparation?", 'a' => "This varies widely by person and exam; consistency across weeks matters far more than any single ideal daily number."],
            ['q' => "Should weaker subjects always get more time than stronger ones?", 'a' => "Generally yes, though maintaining strong subjects with shorter regular sessions is still important."],
            ['q' => "What if the plan falls apart after a few days?", 'a' => "This is common; adjust the plan to be more realistic rather than abandoning structured planning altogether."],
        ],
        'conclusion' => "Good time management for competitive exams is less about finding more hours and more about distributing the hours already available in a balanced, realistic way.",
    ];

    $articles[] = [
        'category' => 'exam-prep', 'image_topic' => 'study', 'image_index' => 3,
        'title' => "How to Analyze Previous Year Question Papers",
        'slug' => "analyze-previous-year-question-papers",
        'excerpt' => "Getting more value from old papers than simply solving them once.",
        'intro' => "Solving previous year question papers is common advice, but simply attempting them once is only the first step. Analyzing the patterns within them adds far more value to exam preparation.",
        'quick_facts' => ["Solve papers under timed, exam-like conditions when possible", "Look for repeated topics across multiple years, not just one paper", "Track which question types cause the most errors", "Revisit the same paper again after a few weeks to measure improvement"],
        'sections' => [
            ['heading' => "Solving Under Realistic Conditions", 'body' => [
                "A previous year paper gives the most useful information when attempted under conditions close to the real exam: a timer, minimal distractions and no reference material, since this reveals genuine readiness rather than comfortable, untimed performance.",
            ]],
            ['heading' => "Looking for Patterns Across Multiple Years", 'body' => [
                "Reviewing several years of papers together, rather than just one, often reveals which topics appear repeatedly and which question formats are consistently used, which helps prioritize study time more effectively.",
            ], 'list' => [
                "List topics that appear across at least three recent years",
                "Note the typical format for numerical or reasoning questions",
                "Identify sections where scores are consistently lower",
            ]],
            ['heading' => "Turning Mistakes Into a Study List", 'body' => [
                "Every incorrect answer is useful information. Keeping a running list of the specific concept behind each mistake, rather than just the question, turns error review into a focused revision plan.",
            ]],
        ],
        'faqs' => [
            ['q' => "How many years of papers should I review?", 'a' => "Reviewing the last five to ten years, where available, usually gives a reliable sense of recurring patterns."],
            ['q' => "Should I memorize answers from old papers?", 'a' => "No, the goal is understanding the underlying concept and pattern, since exact questions are rarely repeated."],
            ['q' => "Is it useful to redo the same paper later?", 'a' => "Yes, redoing a paper after a few weeks of study is a good way to measure genuine improvement."],
        ],
        'conclusion' => "Previous year papers are most valuable when treated as a source of patterns and honest feedback, not simply as extra practice questions to complete once.",
    ];

    // ---- Results ------------------------------------------------------
    $articles[] = [
        'category' => 'results', 'image_topic' => 'exam', 'image_index' => 0,
        'title' => "How Merit Lists Are Prepared: A Simple Explanation",
        'slug' => "how-merit-lists-are-prepared",
        'excerpt' => "A plain overview of how candidates are ranked and shortlisted after an exam.",
        'intro' => "A merit list ranks candidates based on their performance, but the exact process behind it often involves more than a simple total score. Understanding the general logic helps in reading a result correctly.",
        'quick_facts' => ["Merit lists are usually based on total marks, sometimes with tie-breaking rules", "Category-based reservations can create separate cut-offs within one list", "A cut-off is the minimum rank or score required to qualify", "The exact method is always defined in the exam's official rules"],
        'sections' => [
            ['heading' => "The Basic Ranking Principle", 'body' => [
                "In most cases, candidates are ranked from the highest total score to the lowest. Where two candidates have the exact same score, exam authorities typically apply a predefined tie-breaking rule, such as age or a specific subject's score, stated in advance in the official rules.",
            ]],
            ['heading' => "Why Cut-Offs Can Differ by Category", 'body' => [
                "Many recruitment and admission processes reserve a portion of seats for specific categories, which means the minimum qualifying score, or cut-off, can differ across categories even within the same single merit list.",
            ], 'list' => [
                "General category cut-off is usually the highest",
                "Reserved category cut-offs are calculated separately",
                "A candidate can sometimes qualify under either category if eligible",
            ]],
            ['heading' => "Reading a Merit List Correctly", 'body' => [
                "A rank alone does not always confirm selection; it needs to be compared against the specific cut-off for the relevant category and the number of available seats or positions for that cycle.",
            ]],
        ],
        'faqs' => [
            ['q' => "Does a higher score always guarantee selection?", 'a' => "Not necessarily; it depends on the number of available seats and the specific cut-off for the relevant category."],
            ['q' => "Can cut-offs change from year to year?", 'a' => "Yes, cut-offs typically vary based on the difficulty of that year's exam and the number of applicants."],
            ['q' => "What if two candidates have the exact same score?", 'a' => "Exam authorities usually apply a predefined tie-breaking rule, stated in the official exam notification."],
        ],
        'conclusion' => "Merit lists follow clear, predefined rules, even though they can look complex at first glance. Reading the official rules alongside the result gives the clearest picture of where a candidate actually stands.",
    ];

    $articles[] = [
        'category' => 'results', 'image_topic' => 'exam', 'image_index' => 1,
        'title' => "What to Do After Your Exam Result Is Published",
        'slug' => "what-to-do-after-exam-result",
        'excerpt' => "A short, practical checklist for the days immediately following a result announcement.",
        'intro' => "The period right after a result is published often involves several follow-up steps, and missing one of them can cause avoidable delays later in the process.",
        'quick_facts' => ["Download and save the official scorecard immediately", "Check for any follow-up steps, such as document verification", "Note any deadlines mentioned on the result page itself", "Keep both digital and printed copies of the result"],
        'sections' => [
            ['heading' => "Immediate Steps Right After the Result", 'body' => [
                "Downloading and saving the official scorecard as soon as it is available is important, since some portals do not keep results accessible indefinitely. A printed copy is also useful for later verification steps.",
            ]],
            ['heading' => "Checking for Next Steps", 'body' => [
                "Many recruitment or admission processes have further stages after an initial result, such as document verification, an interview, or a counseling round. The result page or an accompanying notice usually states this clearly.",
            ], 'list' => [
                "Look for any mentioned next stage, such as verification or counseling",
                "Note any deadlines connected to that next stage",
                "Prepare documents for verification well in advance",
            ]],
            ['heading' => "If the Result Was Not as Expected", 'body' => [
                "Reviewing the specific answer key or scorecard breakdown, where one is provided, can clarify exactly where marks were lost, which is useful information for a future attempt.",
            ]],
        ],
        'faqs' => [
            ['q' => "How long are result pages usually kept online?", 'a' => "This varies significantly; downloading a copy immediately is the safest approach."],
            ['q' => "Is a printout of the result page accepted everywhere?", 'a' => "Most processes accept it, but always check the specific requirement for any following stage."],
            ['q' => "What if there appears to be an error in my result?", 'a' => "Contact the exam authority's official helpdesk through the channel listed on their website."],
        ],
        'conclusion' => "A result announcement is often just one step in a longer process. Acting quickly on the immediate next steps keeps the overall process moving smoothly.",
    ];

    $articles[] = [
        'category' => 'results', 'image_topic' => 'exam', 'image_index' => 2,
        'title' => "Understanding Normalization and Scaling in Competitive Exams",
        'slug' => "normalization-scaling-competitive-exams",
        'excerpt' => "Why exams held across multiple shifts sometimes adjust raw scores, explained simply.",
        'intro' => "Many large-scale competitive exams are conducted across multiple shifts or days, using different question sets for each. Normalization is the method used to make results from these different sets fairly comparable.",
        'quick_facts' => ["Normalization is mainly used when an exam has multiple shifts or sets", "It adjusts for slight differences in difficulty between shifts", "A normalized score can differ from the raw number of correct answers", "The exact formula is defined by the specific exam authority"],
        'sections' => [
            ['heading' => "Why Normalization Is Needed at All", 'body' => [
                "When different candidates attempt different question sets, even carefully designed sets can vary slightly in difficulty. Without adjustment, a candidate in an easier shift could appear to score higher purely due to that difference, rather than genuine performance.",
            ]],
            ['heading' => "The General Idea Behind the Adjustment", 'body' => [
                "Rather than comparing raw scores directly, normalization typically looks at a candidate's relative performance within their own shift, then adjusts it onto a common scale that can be fairly compared across all shifts.",
            ]],
            ['heading' => "What This Means for a Candidate", 'body' => [
                "A normalized score can be slightly different from a simple count of correct answers, and this is expected, not an error. The official result document usually explains, or links to, the specific method used for that exam.",
            ], 'list' => [
                "A normalized score is not the same as a raw score",
                "Both figures may be shown separately on some result documents",
                "The specific formula is published by the exam authority when normalization is used",
            ]],
        ],
        'faqs' => [
            ['q' => "Is normalization used in every competitive exam?", 'a' => "No, it is generally only used for exams conducted across multiple shifts or sets."],
            ['q' => "Can normalization lower a candidate's score compared to their raw marks?", 'a' => "Yes, it is a relative adjustment, so it can move a score up or down slightly depending on shift-level performance."],
            ['q' => "Where can I find the exact normalization formula used?", 'a' => "The specific exam authority typically publishes this in an official notice alongside or before the result."],
        ],
        'conclusion' => "Normalization exists to keep multi-shift exams fair, not to disadvantage any specific candidate. Understanding the concept helps in interpreting a result correctly rather than assuming an error.",
    ];

    // ---- Scholarships ---------------------------------------------------
    $articles[] = [
        'category' => 'scholarships', 'image_topic' => 'scholarship', 'image_index' => 0,
        'title' => "Types of Scholarships Explained: Merit, Need and Government",
        'slug' => "types-of-scholarships-explained",
        'excerpt' => "The main categories of scholarships and how their eligibility criteria usually differ.",
        'intro' => "Scholarships are often grouped into a few broad types, and understanding the difference helps in identifying which ones a student is genuinely eligible for, rather than applying broadly without a clear match.",
        'quick_facts' => ["Merit-based scholarships focus mainly on academic performance", "Need-based scholarships consider family income and financial circumstances", "Government scholarships often combine both merit and specific category criteria", "Some scholarships are subject or field-specific"],
        'sections' => [
            ['heading' => "Merit-Based Scholarships", 'body' => [
                "These are awarded primarily based on academic performance, such as exam scores or grades, and are usually open to a wide range of applicants who meet a minimum performance threshold.",
            ]],
            ['heading' => "Need-Based Scholarships", 'body' => [
                "These focus on financial circumstances rather than academic ranking alone, and typically require income proof or similar documentation from the applicant's family to establish eligibility.",
            ]],
            ['heading' => "Government and Category-Specific Scholarships", 'body' => [
                "Many government scholarship schemes combine a minimum academic requirement with additional criteria, such as category, region or field of study, and are usually announced with a specific application window each year.",
            ], 'list' => [
                "Check both the academic and non-academic eligibility criteria",
                "Government scholarships often require specific supporting documents",
                "Application windows are usually fixed and do not extend easily",
            ]],
        ],
        'faqs' => [
            ['q' => "Can a student apply for more than one type of scholarship?", 'a' => "In most cases yes, as long as the eligibility criteria for each are genuinely met and there is no stated restriction against combining them."],
            ['q' => "Are merit-based scholarships only for top rank holders?", 'a' => "Not always; many set a reasonable minimum threshold rather than requiring the very top rank."],
            ['q' => "How do I find out which scholarships I am eligible for?", 'a' => "Reviewing official government and institutional scholarship portals directly is the most reliable method."],
        ],
        'conclusion' => "Understanding the different scholarship categories helps in applying to the ones that genuinely match a student's situation, rather than spending time on applications with little chance of success.",
    ];

    $articles[] = [
        'category' => 'scholarships', 'image_topic' => 'scholarship', 'image_index' => 1,
        'title' => "How to Write a Strong Scholarship Application Essay",
        'slug' => "strong-scholarship-application-essay",
        'excerpt' => "What scholarship committees generally look for in a personal statement or essay.",
        'intro' => "Many scholarship applications include a short essay or personal statement. This is often the only part of the application where a student's own voice, rather than just grades and documents, comes through.",
        'quick_facts' => ["Answer the exact question asked, not a general version of it", "Specific examples are more convincing than broad claims", "Keep the essay within the stated word limit", "Proofread carefully; small errors distract from a strong essay"],
        'sections' => [
            ['heading' => "Understanding What Is Actually Being Asked", 'body' => [
                "Scholarship essay prompts often look similar but ask for slightly different things, such as a personal challenge overcome, a future goal, or a specific achievement. Reading the exact prompt carefully before writing avoids submitting an essay that misses the actual question.",
            ]],
            ['heading' => "Using Specific Examples Over General Statements", 'body' => [
                "A general statement like being hardworking is far less convincing than a specific example that demonstrates it, such as a particular project, challenge or achievement with a clear outcome.",
            ], 'list' => [
                "Choose one or two specific examples rather than many vague ones",
                "Explain what you learned or how you grew from the example",
                "Connect the example back to your future goals where relevant",
            ]],
            ['heading' => "Reviewing and Polishing the Final Draft", 'body' => [
                "Reading the essay aloud, and ideally having someone else review it, catches errors and awkward phrasing that are easy to miss after writing something repeatedly.",
            ]],
        ],
        'faqs' => [
            ['q' => "How long should a scholarship essay be?", 'a' => "Always follow the stated word or character limit exactly; going significantly over or under it can count against an application."],
            ['q' => "Is it acceptable to reuse the same essay for multiple scholarships?", 'a' => "Only if the prompts genuinely match; otherwise adapting the essay to each specific question is important."],
            ['q' => "Should the essay focus on hardship or achievement?", 'a' => "This depends entirely on what the specific prompt asks; answer the actual question rather than a default format."],
        ],
        'conclusion' => "A strong scholarship essay directly answers the specific question asked and backs its points with genuine, specific examples rather than broad, generic statements.",
    ];

    $articles[] = [
        'category' => 'scholarships', 'image_topic' => 'scholarship', 'image_index' => 2,
        'title' => "Common Scholarship Application Mistakes to Avoid",
        'slug' => "common-scholarship-application-mistakes",
        'excerpt' => "The recurring errors that cause otherwise strong scholarship applications to be rejected.",
        'intro' => "Scholarship committees review large numbers of applications, and small, avoidable mistakes are often enough to remove an otherwise qualified application from consideration.",
        'quick_facts' => ["Missing a document is one of the most common rejection reasons", "Generic essays that ignore the specific prompt weaken an application", "Submitting close to the deadline increases the risk of a technical error", "Every eligibility criterion should be checked before applying, not assumed"],
        'sections' => [
            ['heading' => "Incomplete or Incorrect Documentation", 'body' => [
                "A missing income certificate, an expired document, or a certificate from the wrong issuing authority are among the most common reasons applications are rejected, often for reasons unrelated to the student's actual eligibility or merit.",
            ]],
            ['heading' => "Ignoring the Specific Prompt or Instructions", 'body' => [
                "Submitting a generic, previously written essay that does not directly address the specific question asked signals to a reviewer that the application was not carefully prepared for that particular scholarship.",
            ]],
            ['heading' => "Submitting at the Last Minute", 'body' => [
                "Portals can experience technical issues close to a deadline, and there is little time left to fix a problem, such as a failed upload, if the submission is attempted only hours before the window closes.",
            ], 'list' => [
                "Aim to submit at least a day or two before the deadline",
                "Double-check every uploaded document before final submission",
                "Save a confirmation or receipt after submitting",
            ]],
        ],
        'faqs' => [
            ['q' => "What if I realize a mistake after submitting?", 'a' => "Contact the scholarship provider immediately through their official channel; some allow corrections, others do not."],
            ['q' => "Does applying for many scholarships at once increase the risk of mistakes?", 'a' => "Yes, rushing through multiple applications increases the chance of an overlooked detail; a checklist per application helps."],
            ['q' => "Is it worth applying if I am not fully sure about eligibility?", 'a' => "Confirm eligibility directly with the provider first, since ineligible applications are rarely reviewed."],
        ],
        'conclusion' => "Most scholarship application mistakes are avoidable with a careful checklist and enough time before the deadline, rather than a lack of genuine merit.",
    ];

    $articles[] = [
        'category' => 'scholarships', 'image_topic' => 'scholarship', 'image_index' => 3,
        'title' => "A Simple Timeline for Scholarship Deadlines and Documents",
        'slug' => "scholarship-deadlines-documents-timeline",
        'excerpt' => "A practical month-by-month approach to staying ready for scholarship application windows.",
        'intro' => "Scholarship application windows are often short and can open with little advance notice. A simple, ongoing timeline of preparation keeps a student ready to apply as soon as a window opens, rather than starting from scratch.",
        'quick_facts' => ["Many scholarship documents take time to issue, plan ahead", "Following official scholarship portals keeps you informed of new windows", "A prepared document folder saves valuable time when a window opens", "Some scholarships repeat annually around the same period"],
        'sections' => [
            ['heading' => "Preparing Documents Well Before a Window Opens", 'body' => [
                "Certain documents, such as income certificates, can take time to process through the issuing office. Requesting these well before an expected application period avoids missing a short window due to a document still being processed.",
            ]],
            ['heading' => "Staying Informed About Upcoming Windows", 'body' => [
                "Following official scholarship portals or an institution's own notice board directly is more reliable than depending on word of mouth, since some scholarships receive relatively little publicity despite being genuinely available.",
            ], 'list' => [
                "Bookmark official national and institutional scholarship portals",
                "Note the approximate month a scholarship opened in previous years",
                "Keep a simple personal calendar of application windows",
            ]],
            ['heading' => "Keeping a Ready-to-Use Document Folder", 'body' => [
                "Maintaining a single, updated folder with current copies of common documents, identity proof, income certificates and academic records, means an application can be completed quickly once a window opens.",
            ]],
        ],
        'faqs' => [
            ['q' => "How early should I start preparing documents?", 'a' => "Ideally a few months before an expected application period, since some certificates take time to issue."],
            ['q' => "Do scholarship windows open at the same time every year?", 'a' => "Many do follow a similar annual pattern, though exact dates can shift; always confirm with the official source."],
            ['q' => "What if a document expires before I apply?", 'a' => "Check the specific validity requirement stated by the scholarship provider and renew it in advance if needed."],
        ],
        'conclusion' => "Scholarship preparation works best as an ongoing habit rather than a rushed, last-minute task. A little planning throughout the year makes short application windows far easier to manage.",
    ];

    // ---- Career Guide -----------------------------------------------------
    $articles[] = [
        'category' => 'career-guide', 'image_topic' => 'career', 'image_index' => 0,
        'title' => "How to Choose the Right Career Path After Graduation",
        'slug' => "choose-right-career-path-after-graduation",
        'excerpt' => "A simple framework for comparing career options instead of choosing based on pressure alone.",
        'intro' => "Choosing a career path right after graduation can feel overwhelming, especially with pressure from family, peers or general trends. A structured comparison, rather than a single quick decision, usually leads to a better long-term fit.",
        'quick_facts' => ["Start with your own interests and strengths, not just trends", "Compare a few realistic options side by side", "Talk to people already working in a field before deciding", "A career decision can be adjusted later, it is rarely final"],
        'sections' => [
            ['heading' => "Starting With Self-Assessment", 'body' => [
                "Before comparing external options, it helps to honestly list subjects or tasks that genuinely hold interest, as well as skills that come more naturally, since sustained motivation is easier in areas that already have some personal pull.",
            ]],
            ['heading' => "Researching Realistic Options", 'body' => [
                "For each option being seriously considered, it is worth researching typical entry requirements, common day-to-day work, and realistic growth over the following five to ten years, rather than only the most visible success stories.",
            ], 'list' => [
                "Typical qualifications or exams required to enter the field",
                "What day-to-day work actually looks like",
                "Realistic growth and stability over time",
            ]],
            ['heading' => "Talking to People Already in the Field", 'body' => [
                "A short conversation with someone actually working in a field often reveals far more practical detail than online research alone, including aspects that are rarely discussed publicly.",
            ]],
        ],
        'faqs' => [
            ['q' => "What if I am interested in more than one field?", 'a' => "This is common; researching the realistic overlap or a combined path can help narrow the decision."],
            ['q' => "Is it a problem to change career direction later?", 'a' => "Many people do adjust their path over time; an initial decision is a starting point, not a permanent constraint."],
            ['q' => "Should I choose based on salary alone?", 'a' => "Salary is one factor among several; long-term satisfaction and stability are equally worth weighing."],
        ],
        'conclusion' => "A career decision made through honest self-assessment and realistic research tends to hold up better over time than one made purely under external pressure or short-term trends.",
    ];

    $articles[] = [
        'category' => 'career-guide', 'image_topic' => 'career', 'image_index' => 1,
        'title' => "Building a Professional Resume and Online Profile",
        'slug' => "professional-resume-online-profile",
        'excerpt' => "How a resume and an online professional profile work together during a job search.",
        'intro' => "A resume and an online professional profile now often work together during a job search, with recruiters frequently checking both. Keeping them consistent and genuinely well organized improves the overall impression significantly.",
        'quick_facts' => ["Keep a resume to one or two pages unless extensive experience justifies more", "An online profile should closely match the resume's core facts", "Use clear, specific descriptions rather than vague job titles alone", "Update both regularly, not only when actively job hunting"],
        'sections' => [
            ['heading' => "Structuring a Clear Resume", 'body' => [
                "A resume works best when it is easy to scan quickly: clear section headings, consistent formatting, and the most relevant and recent experience placed near the top rather than buried further down.",
            ], 'list' => [
                "Contact details and a short summary at the top",
                "Experience listed with clear outcomes, not just duties",
                "Education and relevant certifications",
                "Skills relevant to the specific role being applied for",
            ]],
            ['heading' => "Keeping an Online Profile Consistent", 'body' => [
                "Recruiters commonly cross-check a resume against an online professional profile, so keeping job titles, dates and key details consistent across both avoids raising unnecessary questions.",
            ]],
            ['heading' => "Tailoring Applications Without Starting From Scratch", 'body' => [
                "Rather than rewriting a resume entirely for each application, adjusting the summary and highlighted skills to match the specific role's requirements is usually enough to make an application feel targeted.",
            ]],
        ],
        'faqs' => [
            ['q' => "How long should a resume be for someone early in their career?", 'a' => "One page is usually sufficient for candidates with limited work experience."],
            ['q' => "Should every past job be listed on a resume?", 'a' => "Only roles relevant to the current career direction are generally worth including, especially as experience grows."],
            ['q' => "Is an online profile necessary if I already have a good resume?", 'a' => "Increasingly, yes, since many recruiters search for candidates or verify applications online before contacting them."],
        ],
        'conclusion' => "A resume and an online profile that are clear, consistent and regularly updated together create a stronger, more trustworthy impression than either one alone.",
    ];

    $articles[] = [
        'category' => 'career-guide', 'image_topic' => 'career', 'image_index' => 2,
        'title' => "Interview Preparation: Common Questions and How to Answer",
        'slug' => "interview-preparation-common-questions",
        'excerpt' => "A practical approach to preparing for the questions that come up in most interviews.",
        'intro' => "While every interview is different, a core set of questions appears frequently across most job interviews. Preparing thoughtful, honest answers to these in advance reduces nervousness and improves overall performance significantly.",
        'quick_facts' => ["Prepare specific examples, not just general statements", "Research the organization before the interview, not during it", "Practice answers out loud, not only in your head", "Prepare a few genuine questions to ask the interviewer"],
        'sections' => [
            ['heading' => "Preparing for Common Question Types", 'body' => [
                "Questions about strengths, weaknesses, past challenges and reasons for applying appear frequently. Preparing a specific, honest example for each, rather than a generic answer, makes responses far more convincing.",
            ], 'list' => [
                "Tell me about yourself",
                "Describe a challenge you faced and how you handled it",
                "Why do you want this role or organization",
                "Where do you see yourself in a few years",
            ]],
            ['heading' => "Researching the Organization Beforehand", 'body' => [
                "A brief understanding of what the organization does, and why it might be a genuine fit, allows for more specific, relevant answers rather than generic ones that could apply to any interview.",
            ]],
            ['heading' => "Practicing Out Loud, Not Just Mentally", 'body' => [
                "Answers that sound clear when thought through silently often come out less smoothly when actually spoken. Practicing out loud, ideally with another person, reveals gaps that silent preparation misses.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is it acceptable to say I do not know an answer?", 'a' => "Yes, honestly acknowledging a gap while showing willingness to learn is usually better than guessing inaccurately."],
            ['q' => "How should I answer a question about weaknesses?", 'a' => "A genuine weakness paired with what you are actively doing to improve it is generally more convincing than a disguised strength."],
            ['q' => "Should I ask questions at the end of an interview?", 'a' => "Yes, a few thoughtful questions show genuine interest and engagement with the role."],
        ],
        'conclusion' => "Interview preparation is less about memorizing perfect answers and more about having genuine, specific examples ready and having practiced presenting them clearly.",
    ];

    $articles[] = [
        'category' => 'career-guide', 'image_topic' => 'career', 'image_index' => 3,
        'title' => "Comparing Public Sector and Private Sector Careers",
        'slug' => "public-sector-vs-private-sector-careers",
        'excerpt' => "The general differences worth considering when weighing a government job against a private sector role.",
        'intro' => "Public sector and private sector careers each offer a distinct set of trade-offs. Neither is universally better; the right choice depends heavily on individual priorities around stability, growth and work environment.",
        'quick_facts' => ["Public sector roles often emphasize job stability and structured benefits", "Private sector roles can offer faster growth but with more variability", "Entry processes differ significantly between the two sectors", "Personal priorities matter more than general opinions about either sector"],
        'sections' => [
            ['heading' => "Stability and Structure", 'body' => [
                "Public sector roles often come with a well-defined structure, including pay scales, promotion timelines and job security, which can appeal strongly to those who value predictability over rapid change.",
            ]],
            ['heading' => "Growth Pace and Flexibility", 'body' => [
                "Private sector roles can sometimes offer faster salary growth or quicker movement into new responsibilities, though this often comes with less predictability and more variation between organizations and industries.",
            ], 'list' => [
                "Public sector: generally slower, more predictable progression",
                "Private sector: can vary widely by company and industry",
                "Both sectors offer long-term career paths when approached deliberately",
            ]],
            ['heading' => "Entry Process Differences", 'body' => [
                "Public sector roles frequently require passing a structured competitive exam, while private sector hiring more commonly relies on resumes, interviews and sometimes skills assessments specific to the role.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is a public sector job always more secure than a private one?", 'a' => "Generally public sector roles offer more structured job security, though this varies by country and specific role."],
            ['q' => "Can someone move between public and private sector careers later?", 'a' => "Yes, though the entry processes differ, and some transferable skills may need to be highlighted differently."],
            ['q' => "Which sector offers better long-term growth?", 'a' => "This depends heavily on the specific field and individual priorities; neither sector is universally better for growth."],
        ],
        'conclusion' => "Comparing public and private sector careers against personal priorities, rather than general assumptions, leads to a more satisfying long-term decision.",
    ];

    // ---- Study Tips (new category) ----------------------------------------
    $articles[] = [
        'category' => 'study-tips', 'image_topic' => 'study', 'image_index' => 0,
        'title' => "The Pomodoro Technique: A Beginner's Guide to Focused Study",
        'slug' => "pomodoro-technique-beginners-guide",
        'excerpt' => "A simple timed-interval method for building focus in short, manageable blocks.",
        'intro' => "The Pomodoro Technique breaks study time into short, focused intervals separated by brief breaks. For students who struggle to sit still for long, unbroken hours, this structure can make starting a session feel far more manageable.",
        'quick_facts' => ["A standard session is 25 minutes of focused work", "A short break of about 5 minutes follows each session", "After four sessions, a longer break is usually taken", "The technique works best with a single, specific task per session"],
        'sections' => [
            ['heading' => "How the Basic Cycle Works", 'body' => [
                "One cycle typically involves 25 minutes of fully focused work on a single task, followed by a five-minute break. After completing four such cycles, a longer break of fifteen to thirty minutes is usually taken before starting again.",
            ], 'list' => [
                "Choose one specific task before starting the timer",
                "Work with full focus for the set interval",
                "Take the short break away from the study material",
                "Repeat, with a longer break after four cycles",
            ]],
            ['heading' => "Why Short Intervals Work Well", 'body' => [
                "A defined, relatively short interval feels far less intimidating to start than an open-ended study session, which is often the actual barrier to beginning. Knowing a break is only 25 minutes away makes sustained focus easier to commit to.",
            ]],
            ['heading' => "Adjusting the Technique to Fit Your Needs", 'body' => [
                "The 25-minute interval is a common starting point, not a fixed rule. Some students find slightly longer or shorter intervals work better for their own concentration span, and adjusting the timing is a normal part of making the technique effective.",
            ]],
        ],
        'faqs' => [
            ['q' => "What if 25 minutes feels too short or too long?", 'a' => "Adjust the interval length to what genuinely suits your own concentration span; the core idea is structured, timed focus."],
            ['q' => "What should be done during the short breaks?", 'a' => "Stepping away from the study material entirely, such as stretching or a short walk, tends to work better than checking a phone."],
            ['q' => "Is this technique suitable for all types of studying?", 'a' => "It works well for most focused tasks, though very long, uninterrupted problem-solving may sometimes need a longer single block."],
        ],
        'conclusion' => "The Pomodoro Technique is a simple, low-cost way to make study sessions feel more approachable, particularly for students who struggle with long, unstructured study blocks.",
    ];

    $articles[] = [
        'category' => 'study-tips', 'image_topic' => 'study', 'image_index' => 1,
        'title' => "How to Take Effective Notes Using the Cornell Method",
        'slug' => "cornell-method-effective-notes",
        'excerpt' => "A structured note-taking layout that makes revision significantly faster later.",
        'intro' => "The Cornell Method is a note-taking layout designed specifically to make later revision faster and more effective, rather than simply capturing information during a class or reading session.",
        'quick_facts' => ["The page is divided into notes, cues and a summary section", "The cue column is filled in after the notes, not during", "A short summary at the bottom aids quick later review", "Works for both handwritten and digital notes"],
        'sections' => [
            ['heading' => "Setting Up the Page Layout", 'body' => [
                "A page is divided into a larger main section for notes taken during the class or reading, a narrower column on the side for cue words or questions, and a short summary section at the bottom.",
            ], 'list' => [
                "Main notes section: written during the class or reading",
                "Cue column: key words or questions, added afterward",
                "Summary section: a few sentences capturing the core idea",
            ]],
            ['heading' => "Filling in the Cue Column Afterward", 'body' => [
                "After the class or reading session, reviewing the main notes and writing a short cue or question in the side column for each key point turns passive notes into an active revision tool.",
            ]],
            ['heading' => "Using the Notes for Later Revision", 'body' => [
                "During revision, covering the main notes column and trying to answer each cue from memory turns the page into a built-in self-testing tool, rather than something to simply reread passively.",
            ]],
        ],
        'faqs' => [
            ['q' => "Does this method work for digital note-taking apps?", 'a' => "Yes, most note-taking apps can be adapted to a similar three-section layout, or a simple table can be used."],
            ['q' => "How soon after class should the cue column be filled in?", 'a' => "As soon as reasonably possible, ideally the same day, while the content is still fresh."],
            ['q' => "Is this method suitable for numerical or technical subjects?", 'a' => "Yes, with formulas or key steps in the main section and short prompts as cues."],
        ],
        'conclusion' => "The Cornell Method takes a small amount of extra structure during note-taking, but pays that effort back many times over when it comes time to revise efficiently before an exam.",
    ];

    $articles[] = [
        'category' => 'study-tips', 'image_topic' => 'study', 'image_index' => 2,
        'title' => "Memory Techniques: Mnemonics, Visualization and Spaced Repetition",
        'slug' => "memory-techniques-mnemonics-spaced-repetition",
        'excerpt' => "Practical memory techniques that go beyond simple repetition.",
        'intro' => "Plain repetition is one of the least efficient ways to memorize information. A handful of well-established memory techniques can make the same amount of study time noticeably more effective.",
        'quick_facts' => ["Mnemonics turn lists into memorable phrases or acronyms", "Visualization connects facts to strong mental images", "Spaced repetition reviews information at increasing intervals", "Combining techniques generally works better than using just one"],
        'sections' => [
            ['heading' => "Using Mnemonics for Lists and Sequences", 'body' => [
                "A mnemonic, such as an acronym made from the first letters of a list, or a short memorable sentence, gives the brain a simple hook to recall a longer sequence of information accurately.",
            ]],
            ['heading' => "Visualization for Abstract Concepts", 'body' => [
                "Connecting an abstract fact to a vivid, even exaggerated, mental image tends to make it far more memorable than the plain fact alone, since the brain generally retains strong images better than plain text.",
            ]],
            ['heading' => "Spaced Repetition Over Time", 'body' => [
                "Reviewing information right after learning it, then again after a day, then a few days, then a week, takes advantage of how memory naturally strengthens with spaced review, rather than one long cram session.",
            ], 'list' => [
                "Review new material within 24 hours of first learning it",
                "Review again after a few days, then after a week",
                "Focus repeated review time on the items that are still difficult",
            ]],
        ],
        'faqs' => [
            ['q' => "Which memory technique is the most effective overall?", 'a' => "Spaced repetition generally has the strongest research support, especially when combined with active recall."],
            ['q' => "Can these techniques be used together?", 'a' => "Yes, combining a mnemonic or visualization with a spaced review schedule often works better than any single technique alone."],
            ['q' => "Are memory techniques useful for understanding, not just memorizing?", 'a' => "They are most effective for facts, sequences and definitions; genuine understanding still requires working through and applying concepts."],
        ],
        'conclusion' => "Memory techniques are tools, not shortcuts around genuine understanding, but used well they can make the memorization part of studying significantly faster and more reliable.",
    ];

    $articles[] = [
        'category' => 'study-tips', 'image_topic' => 'study', 'image_index' => 3,
        'title' => "How to Stay Motivated During Long Exam Preparation",
        'slug' => "stay-motivated-long-exam-preparation",
        'excerpt' => "Practical ways to sustain motivation over months, not just for a single study session.",
        'intro' => "Long exam preparation periods, sometimes lasting many months, present a different challenge from a single study session: sustaining motivation consistently, even through weeks that feel slow or discouraging.",
        'quick_facts' => ["Break a large goal into smaller, visible milestones", "Track progress, not just remaining work", "Expect motivation to fluctuate, it is normal, not a failure", "Connect daily study tasks back to the larger goal regularly"],
        'sections' => [
            ['heading' => "Breaking a Large Goal Into Milestones", 'body' => [
                "A distant exam date can feel abstract and demotivating. Breaking preparation into smaller monthly or weekly milestones creates more frequent, visible progress markers that are easier to stay motivated around.",
            ]],
            ['heading' => "Tracking Progress Instead of Only Remaining Work", 'body' => [
                "It is easy to focus only on how much material remains, which can feel discouraging. Keeping a simple log of topics already completed provides a more balanced, motivating view of genuine progress made.",
            ], 'list' => [
                "Keep a simple weekly log of topics completed",
                "Review the log during low-motivation periods as evidence of progress",
                "Celebrate small milestones, not only the final result",
            ]],
            ['heading' => "Accepting That Motivation Naturally Fluctuates", 'body' => [
                "Even highly disciplined students experience low-motivation periods during long preparation. Building consistent habits and routines that do not depend entirely on daily motivation helps preparation continue through these normal dips.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is it normal to lose motivation partway through preparation?", 'a' => "Yes, this is extremely common; the key is having routines that do not depend entirely on daily motivation."],
            ['q' => "How can I make progress feel more visible?", 'a' => "A simple log or checklist of completed topics makes progress concrete rather than abstract."],
            ['q' => "Should I take breaks during long-term preparation?", 'a' => "Yes, planned rest periods generally support sustained motivation better than continuous, unbroken study."],
        ],
        'conclusion' => "Sustained motivation over a long preparation period comes less from willpower alone and more from a structure of visible milestones, tracked progress and accepted, planned rest.",
    ];

    $articles[] = [
        'category' => 'study-tips', 'image_topic' => 'study', 'image_index' => 0,
        'title' => "How to Build a Realistic Daily Study Routine",
        'slug' => "build-realistic-daily-study-routine",
        'excerpt' => "Designing a daily schedule that can actually be followed consistently, not just on paper.",
        'intro' => "Many study routines look impressive on paper but fail within a week because they are simply not realistic for the person following them. A sustainable routine starts from an honest look at daily life, not an ideal version of it.",
        'quick_facts' => ["Base a routine on your actual daily schedule, not an idealized one", "Include fixed times for meals, rest and other commitments", "Start with a routine slightly below your maximum capacity", "Review and adjust the routine every one to two weeks"],
        'sections' => [
            ['heading' => "Starting From an Honest Daily Schedule", 'body' => [
                "Listing existing daily commitments, such as classes, work, travel time and sleep, before adding study blocks, creates a routine grounded in reality rather than an idealized version that ignores genuine constraints.",
            ]],
            ['heading' => "Setting a Sustainable, Not Maximum, Study Load", 'body' => [
                "A routine planned at absolute maximum capacity tends to break down quickly, since it leaves no room for an off day. Planning slightly below capacity, with room to occasionally extend, tends to hold up far better over weeks.",
            ], 'list' => [
                "List fixed commitments first: sleep, meals, travel, other responsibilities",
                "Add study blocks around them, not on top of them",
                "Leave some buffer time for unexpected interruptions",
            ]],
            ['heading' => "Reviewing and Adjusting the Routine Regularly", 'body' => [
                "A routine is rarely perfect on the first attempt. Reviewing it every week or two, and honestly adjusting parts that consistently do not work, is a normal and necessary part of building a sustainable long-term schedule.",
            ]],
        ],
        'faqs' => [
            ['q' => "How many hours should a daily study routine include?", 'a' => "This depends heavily on individual circumstances; a sustainable, consistent number is more valuable than an ambitious but unrealistic one."],
            ['q' => "What if I keep failing to follow my routine?", 'a' => "This usually signals the routine itself is not realistic; simplifying it is often more effective than trying harder to follow an unrealistic plan."],
            ['q' => "Should weekends follow the same routine as weekdays?", 'a' => "Not necessarily; many students use a lighter or different structure on weekends, as long as it remains consistent."],
        ],
        'conclusion' => "A study routine that can actually be followed consistently, even if modest, produces far better long-term results than an ambitious one that collapses within days.",
    ];

    // ---- Skill Development (new category) ---------------------------------
    $articles[] = [
        'category' => 'skill-development', 'image_topic' => 'skills', 'image_index' => 0,
        'title' => "Essential Digital Skills for Today's Job Market",
        'slug' => "essential-digital-skills-job-market",
        'excerpt' => "A practical overview of the digital skills that are broadly useful across most modern jobs.",
        'intro' => "Digital skills are now expected in most jobs, not only technical roles. A relatively small, practical set of digital skills can significantly improve employability across a wide range of fields.",
        'quick_facts' => ["Basic spreadsheet skills are useful across almost every field", "Comfortable, professional written communication is a core digital skill", "Understanding basic online safety protects both you and your employer", "Digital skills can be learned gradually, not all at once"],
        'sections' => [
            ['heading' => "Core Office and Productivity Tools", 'body' => [
                "Comfort with spreadsheets, document editors and basic presentation tools remains one of the most broadly useful digital skill sets, applicable across administrative, technical and management roles alike.",
            ], 'list' => [
                "Basic spreadsheet formulas and simple data organization",
                "Clear, well-formatted document writing",
                "Simple, effective presentation slides",
            ]],
            ['heading' => "Professional Digital Communication", 'body' => [
                "Writing clear, professional emails and messages, and understanding basic video-call etiquette, has become a genuinely important workplace skill as more communication happens through digital channels.",
            ]],
            ['heading' => "Basic Online Safety Awareness", 'body' => [
                "Recognizing suspicious links or messages, using strong and unique passwords, and understanding what information should not be shared publicly are practical skills relevant to almost any digital job today.",
            ]],
        ],
        'faqs' => [
            ['q' => "Do I need to learn coding to be considered digitally skilled?", 'a' => "No, coding is valuable for specific technical roles, but general digital skills are broader and relevant to nearly all jobs."],
            ['q' => "Where can these basic digital skills be learned?", 'a' => "Many free tutorials and guides are available online; consistent, small practice sessions are usually enough to build genuine comfort."],
            ['q' => "Are these skills useful even for non-office jobs?", 'a' => "Increasingly yes, as digital tools are used across retail, healthcare, education and many other fields."],
        ],
        'conclusion' => "A practical, broadly applicable set of digital skills is now a genuine advantage across almost every job market, and can be built gradually through consistent, small practice.",
    ];

    $articles[] = [
        'category' => 'skill-development', 'image_topic' => 'skills', 'image_index' => 1,
        'title' => "How to Improve Communication Skills for Interviews and Work",
        'slug' => "improve-communication-skills-interviews-work",
        'excerpt' => "Practical ways to build clearer, more confident communication over time.",
        'intro' => "Strong communication skills are consistently valued across nearly every job and interview process, yet they are rarely taught directly. A few practical habits can noticeably improve clarity and confidence over time.",
        'quick_facts' => ["Clear, simple language is usually more effective than complex vocabulary", "Active listening is as important as speaking clearly", "Practicing out loud builds confidence faster than silent preparation", "Written and spoken communication both benefit from the same core habits"],
        'sections' => [
            ['heading' => "Speaking Clearly and Simply", 'body' => [
                "Using clear, direct language, rather than unnecessarily complex vocabulary, generally communicates more effectively and is more likely to be understood correctly the first time, both in interviews and everyday work.",
            ]],
            ['heading' => "Practicing Active Listening", 'body' => [
                "Good communication is not only about speaking well; genuinely listening, asking clarifying questions and responding directly to what was actually said builds trust and avoids frequent misunderstandings.",
            ], 'list' => [
                "Let the other person finish before responding",
                "Ask a clarifying question if something is unclear",
                "Summarize back key points in your own words when useful",
            ]],
            ['heading' => "Building Confidence Through Practice", 'body' => [
                "Rehearsing common workplace or interview scenarios out loud, even alone, builds a level of comfort that silent mental preparation alone rarely achieves, and this comfort becomes noticeable in real conversations.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is communication a skill that can genuinely be improved?", 'a' => "Yes, like most skills, consistent practice over time leads to noticeable improvement."],
            ['q' => "How can I practice communication skills without a formal course?", 'a' => "Practicing conversations out loud, reading aloud, and seeking honest feedback from others are all effective, low-cost methods."],
            ['q' => "Does written communication matter as much as spoken communication?", 'a' => "Increasingly yes, since much workplace communication now happens through email and messaging."],
        ],
        'conclusion' => "Communication skills improve steadily with deliberate practice, not through natural talent alone, making them one of the most learnable and valuable workplace skills available.",
    ];

    $articles[] = [
        'category' => 'skill-development', 'image_topic' => 'skills', 'image_index' => 2,
        'title' => "Time Management Skills for Students and Working Professionals",
        'slug' => "time-management-skills-students-professionals",
        'excerpt' => "Core time management principles that apply whether you are studying or working.",
        'intro' => "Time management challenges look similar whether the setting is study or work: too many demands competing for a limited number of hours. A small set of core principles applies well across both situations.",
        'quick_facts' => ["Prioritizing tasks matters more than simply working faster", "Not all tasks deserve equal time and attention", "Planning the next day the night before saves decision time", "Regularly reviewing what worked helps refine the approach over time"],
        'sections' => [
            ['heading' => "Prioritizing Before Scheduling", 'body' => [
                "Before filling a calendar with tasks, sorting them by genuine importance and urgency prevents time from being consumed by low-value tasks simply because they were easier or arrived first.",
            ]],
            ['heading' => "Planning the Next Day in Advance", 'body' => [
                "Spending a few minutes each evening planning the next day's key priorities removes the need to make repeated small decisions throughout a busy day, which itself consumes mental energy and time.",
            ], 'list' => [
                "List the top few priorities for the next day",
                "Estimate roughly how much time each will realistically take",
                "Leave some buffer time for unexpected tasks",
            ]],
            ['heading' => "Reviewing What Actually Worked", 'body' => [
                "A brief weekly review of what was accomplished, and what consistently got pushed aside, reveals patterns that a daily view alone often misses, and helps refine the overall approach over time.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is time management mainly about working faster?", 'a' => "No, it is primarily about prioritizing effectively, since not all tasks deserve equal time and attention."],
            ['q' => "How can I avoid constantly feeling behind on tasks?", 'a' => "Regular prioritization and realistic planning, rather than attempting to do everything, generally reduces this feeling significantly."],
            ['q' => "Does time management apply the same way to study and work?", 'a' => "The core principles are similar, though the specific tasks and constraints differ between the two settings."],
        ],
        'conclusion' => "Effective time management is less about squeezing in more hours and more about consistently prioritizing what actually matters within the hours already available.",
    ];

    $articles[] = [
        'category' => 'skill-development', 'image_topic' => 'skills', 'image_index' => 3,
        'title' => "How to Learn a New Skill Efficiently: A Simple Framework",
        'slug' => "learn-new-skill-efficiently-framework",
        'excerpt' => "A practical, repeatable approach for picking up any new skill more efficiently.",
        'intro' => "Learning a genuinely new skill can feel overwhelming without a clear starting structure. A simple, repeatable framework makes the process far more approachable, regardless of the specific skill being learned.",
        'quick_facts' => ["Break a skill down into its core, most useful sub-parts first", "Practice the most commonly used parts before rare edge cases", "Regular, shorter practice sessions beat occasional long ones", "Seeking feedback early speeds up genuine improvement"],
        'sections' => [
            ['heading' => "Breaking the Skill Into Core Parts", 'body' => [
                "Nearly every skill can be broken into a smaller set of core sub-skills that are used most frequently. Identifying and focusing on these first, rather than trying to master everything at once, speeds up genuine early progress.",
            ]],
            ['heading' => "Practicing Consistently in Shorter Sessions", 'body' => [
                "Regular, shorter practice sessions spread across several days generally build a new skill more effectively than occasional long sessions, since spaced practice supports stronger, more durable learning over time.",
            ], 'list' => [
                "Identify the most commonly used core parts of the skill first",
                "Practice consistently, even in short daily sessions",
                "Track specific areas that still feel difficult",
            ]],
            ['heading' => "Seeking Feedback Early", 'body' => [
                "Getting honest feedback early in the learning process, rather than waiting until a skill feels fully developed, catches incorrect habits before they become deeply ingrained and harder to correct later.",
            ]],
        ],
        'faqs' => [
            ['q' => "How long does it typically take to learn a new skill?", 'a' => "This varies enormously by skill and by person; consistent practice matters far more than a fixed universal timeline."],
            ['q' => "Is it better to focus on one skill at a time?", 'a' => "Generally yes, since divided attention across many new skills at once tends to slow progress on all of them."],
            ['q' => "What if progress feels slow at first?", 'a' => "This is a normal part of early skill-building; consistent practice over weeks usually shows more noticeable progress than any single session."],
        ],
        'conclusion' => "A simple framework of breaking a skill down, practicing consistently and seeking early feedback works well across almost any new skill, regardless of the specific subject.",
    ];

    $articles[] = [
        'category' => 'skill-development', 'image_topic' => 'skills', 'image_index' => 0,
        'title' => "Basic Computer Skills Every Job Seeker Should Know",
        'slug' => "basic-computer-skills-job-seekers",
        'excerpt' => "A practical, entry-level list of computer skills expected in most modern workplaces.",
        'intro' => "Even for roles that are not primarily technical, a basic level of computer literacy is now commonly expected. This overview covers the practical, entry-level skills most often assumed by employers today.",
        'quick_facts' => ["Basic file and folder organization is a foundational skill", "Comfort with email, including attachments, is widely expected", "Simple internet research skills save significant time", "These skills can be learned through free online resources"],
        'sections' => [
            ['heading' => "File and Folder Organization", 'body' => [
                "Knowing how to create, name, organize and locate files and folders efficiently is a foundational skill that underlies almost every other digital task in a modern workplace.",
            ]],
            ['heading' => "Email and Basic Communication Tools", 'body' => [
                "Comfort with composing clear emails, attaching documents correctly, and understanding basic etiquette such as appropriate subject lines is widely expected, even in roles that are not office-based.",
            ], 'list' => [
                "Composing clear, professional emails",
                "Attaching and downloading files correctly",
                "Basic calendar or scheduling tool usage",
            ]],
            ['heading' => "Effective Internet Research", 'body' => [
                "Being able to search for information efficiently, and to judge whether a source is reliable, is an increasingly valuable skill across nearly every type of job, not only research-focused roles.",
            ]],
        ],
        'faqs' => [
            ['q' => "Are these skills necessary even for non-office jobs?", 'a' => "Increasingly yes, as scheduling, communication and record-keeping across many industries now involve basic digital tools."],
            ['q' => "How can someone with limited computer experience start learning?", 'a' => "Many free, beginner-friendly tutorials are available online; starting with the most immediately useful tasks is a practical approach."],
            ['q' => "Is it necessary to be an expert in these tools?", 'a' => "No, a comfortable working knowledge is generally sufficient for most entry-level roles."],
        ],
        'conclusion' => "Basic computer literacy has become a practical baseline expectation across most jobs, and these foundational skills can be built gradually with free, widely available resources.",
    ];

    // ---- Current Affairs (new category) ------------------------------------
    $articles[] = [
        'category' => 'current-affairs', 'image_topic' => 'news', 'image_index' => 0,
        'title' => "How to Build a Daily Current Affairs Reading Habit",
        'slug' => "daily-current-affairs-reading-habit",
        'excerpt' => "A simple, sustainable approach to following current affairs consistently, without it becoming overwhelming.",
        'intro' => "Current affairs preparation often fails not from a lack of information, but from an inconsistent reading habit. A small, sustainable daily routine tends to work far better than occasional long reading sessions.",
        'quick_facts' => ["A short, consistent daily habit beats occasional long sessions", "Focus on a small number of reliable sources", "Note down key points rather than only reading passively", "Review notes weekly to reinforce what was read"],
        'sections' => [
            ['heading' => "Choosing a Small Set of Reliable Sources", 'body' => [
                "Following too many sources at once often leads to repetitive information and wasted time. Selecting a small, reliable set, such as one newspaper and one dedicated current affairs summary, is usually more effective.",
            ]],
            ['heading' => "Building a Consistent Daily Routine", 'body' => [
                "A fixed, even short, daily time slot for current affairs reading builds a habit far more reliably than an intention to catch up occasionally, which often does not happen consistently in practice.",
            ], 'list' => [
                "Set a fixed, realistic daily time slot",
                "Keep the session short but consistent, rather than long but occasional",
                "Note key points immediately rather than relying on memory alone",
            ]],
            ['heading' => "Turning Reading Into Retained Knowledge", 'body' => [
                "Simply reading news once is rarely enough for exam-level retention. Briefly noting key points, and reviewing them weekly, turns daily reading into knowledge that is actually retained under exam conditions.",
            ]],
        ],
        'faqs' => [
            ['q' => "How much time should be spent daily on current affairs?", 'a' => "This varies by exam requirement, but a short, consistent daily session is generally more sustainable than occasional long ones."],
            ['q' => "Is it necessary to follow multiple news sources?", 'a' => "Not necessarily; a small, reliable set is often more effective than trying to follow many sources at once."],
            ['q' => "How can current affairs be retained for exams months later?", 'a' => "Regular note review, rather than reading alone, significantly improves long-term retention."],
        ],
        'conclusion' => "A short, consistent daily reading habit, combined with brief notes and regular review, is generally more effective for current affairs preparation than infrequent, long reading sessions.",
    ];

    $articles[] = [
        'category' => 'current-affairs', 'image_topic' => 'news', 'image_index' => 1,
        'title' => "How to Take Useful Notes From Newspapers for Exams",
        'slug' => "notes-from-newspapers-for-exams",
        'excerpt' => "A practical method for turning newspaper reading into exam-ready notes.",
        'intro' => "Reading a newspaper cover to cover rarely produces exam-ready knowledge on its own. A focused note-taking method turns general reading into a genuinely useful, reviewable resource.",
        'quick_facts' => ["Not every article needs to be noted in detail", "Focus notes on facts, figures and named events", "Organize notes by topic, not just by date", "Short, factual notes are easier to revise than long summaries"],
        'sections' => [
            ['heading' => "Deciding What Is Actually Worth Noting", 'body' => [
                "Not every article carries exam-relevant information. Focusing note-taking specifically on facts, figures, named appointments, and significant events reduces wasted effort on less relevant content.",
            ]],
            ['heading' => "Organizing Notes by Topic", 'body' => [
                "Grouping notes by topic, such as economy, environment or governance, rather than strictly by the date they were read, makes later revision far more efficient, since related facts appear together.",
            ], 'list' => [
                "Group notes under clear topic headings",
                "Record specific facts, figures and names accurately",
                "Keep individual notes short and factual",
            ]],
            ['heading' => "Reviewing Notes Regularly", 'body' => [
                "Notes that are written but never reviewed provide little exam value. A short weekly review session keeps the accumulated notes fresh and genuinely useful when exam time approaches.",
            ]],
        ],
        'faqs' => [
            ['q' => "Should notes be handwritten or typed?", 'a' => "Either works well; the key factor is genuine consistency and regular review rather than the specific format."],
            ['q' => "How long should an individual note be?", 'a' => "Short and factual notes are generally easier to revise quickly than long, detailed summaries."],
            ['q' => "Is it useful to note opinion pieces as well as factual news?", 'a' => "This depends on the specific exam; factual news is usually the higher priority for most current affairs sections."],
        ],
        'conclusion' => "Organized, topic-based notes taken consistently from reliable sources turn ordinary newspaper reading into a genuinely useful exam preparation resource.",
    ];

    $articles[] = [
        'category' => 'current-affairs', 'image_topic' => 'news', 'image_index' => 2,
        'title' => "Static GK vs Current Affairs: Understanding the Difference",
        'slug' => "static-gk-vs-current-affairs",
        'excerpt' => "Why exams separate these two categories, and how to prepare for each differently.",
        'intro' => "Many exam syllabi list both static general knowledge and current affairs as separate sections. Understanding the distinction helps in preparing for each with the right approach, rather than treating them identically.",
        'quick_facts' => ["Static GK covers facts that generally do not change over time", "Current affairs covers recent, time-bound events", "Static GK can be prepared well in advance", "Current affairs requires ongoing, regular preparation closer to the exam"],
        'sections' => [
            ['heading' => "What Static General Knowledge Covers", 'body' => [
                "Static GK typically includes topics such as history, geography, well-established scientific facts and constitutional or structural information, which generally remain unchanged over long periods and can be studied well in advance.",
            ]],
            ['heading' => "What Current Affairs Covers", 'body' => [
                "Current affairs focuses on recent developments, such as recent appointments, events, government schemes or notable news within roughly the past six to twelve months, depending on the specific exam's requirements.",
            ]],
            ['heading' => "Why the Preparation Approach Differs", 'body' => [
                "Because static GK does not change quickly, it can be studied early and revised periodically. Current affairs, by contrast, requires an ongoing, regular reading habit that continues right up until the exam.",
            ], 'list' => [
                "Static GK: study early, revise periodically",
                "Current affairs: requires continuous, regular attention",
                "Both often overlap for topics like recent government schemes",
            ]],
        ],
        'faqs' => [
            ['q' => "Can static GK preparation be completed and then forgotten about?", 'a' => "Periodic revision is still recommended, even though the facts themselves do not change."],
            ['q' => "How far back should current affairs preparation typically go?", 'a' => "This varies by exam; checking the specific exam's syllabus or previous papers gives the most accurate guidance."],
            ['q' => "Do static GK and current affairs ever overlap?", 'a' => "Yes, for example a recently announced scheme can appear in both current affairs and later become a static fact."],
        ],
        'conclusion' => "Recognizing the difference between static GK and current affairs allows for a more efficient study plan, with static topics studied early and current affairs maintained continuously.",
    ];

    $articles[] = [
        'category' => 'current-affairs', 'image_topic' => 'news', 'image_index' => 3,
        'title' => "How to Revise a Full Year of Current Affairs Before an Exam",
        'slug' => "revise-year-current-affairs-before-exam",
        'excerpt' => "A structured approach to condensing months of current affairs into manageable final revision.",
        'intro' => "As an exam approaches, revising an entire year of accumulated current affairs notes can feel overwhelming. A structured, month-by-month condensing approach makes this final revision far more manageable.",
        'quick_facts' => ["Condense notes month by month, not all at once", "Focus final revision on major, recurring themes", "Practice with current affairs quizzes closer to the exam", "A condensed summary is more useful than the original full notes at this stage"],
        'sections' => [
            ['heading' => "Condensing Notes Month by Month", 'body' => [
                "Rather than attempting to review an entire year of notes in one sitting, working through them month by month and condensing each into a short summary makes the overall volume far more manageable.",
            ]],
            ['heading' => "Identifying Major Recurring Themes", 'body' => [
                "Certain themes, such as major government schemes, significant international events or notable appointments, tend to recur across multiple months. Grouping these together in final revision reinforces the most exam-relevant material.",
            ], 'list' => [
                "Government schemes and policy announcements",
                "Notable appointments and awards",
                "Major national and international events",
            ]],
            ['heading' => "Testing Retention With Practice Questions", 'body' => [
                "In the final weeks before an exam, attempting current affairs quizzes or practice questions reveals genuine retention gaps far more effectively than simply rereading condensed notes alone.",
            ]],
        ],
        'faqs' => [
            ['q' => "How far in advance should this final revision start?", 'a' => "Starting condensed monthly revision a few weeks before the exam is generally more manageable than leaving it to the final days."],
            ['q' => "Is it necessary to remember every single event from the year?", 'a' => "No, prioritizing major, recurring and exam-relevant themes is more efficient than attempting to recall every detail."],
            ['q' => "Are current affairs quizzes actually useful this close to an exam?", 'a' => "Yes, they are an effective way to identify specific gaps that still need review in the remaining time."],
        ],
        'conclusion' => "Condensing a full year of current affairs month by month, focused on recurring themes and tested through practice questions, turns an overwhelming task into a manageable final revision plan.",
    ];

    $articles[] = [
        'category' => 'current-affairs', 'image_topic' => 'news', 'image_index' => 0,
        'title' => "Reliable Habits for Fact-Checking News Before You Trust It",
        'slug' => "fact-checking-news-reliable-habits",
        'excerpt' => "Simple habits for verifying information before relying on it for exam preparation or daily decisions.",
        'intro' => "With information spreading quickly through social media and messaging apps, developing simple fact-checking habits protects both exam preparation and everyday decision-making from inaccurate or outdated claims.",
        'quick_facts' => ["Check whether a claim appears on more than one reliable source", "Be cautious of news shared without a clear original source", "Official government or institutional websites are usually the most reliable", "A recent date does not automatically mean a claim is accurate"],
        'sections' => [
            ['heading' => "Checking for Multiple Reliable Sources", 'body' => [
                "A genuine, significant news event is almost always covered by more than one established, reliable source. A claim that appears only in a single forwarded message, with no other coverage, deserves extra caution before being accepted.",
            ]],
            ['heading' => "Identifying the Original Source", 'body' => [
                "Forwarded messages and social media posts often strip away the original source of a claim. Tracing a piece of news back to its original, named publication or official statement is one of the most reliable verification steps available.",
            ], 'list' => [
                "Look for the original publication or official statement",
                "Be cautious of screenshots without a visible, checkable source",
                "Cross-check surprising or significant claims before sharing them further",
            ]],
            ['heading' => "Relying on Official Sources for Important Decisions", 'body' => [
                "For anything related to exams, applications or official processes, government and institutional websites remain the most reliable source, even when other coverage of the same topic is available elsewhere.",
            ]],
        ],
        'faqs' => [
            ['q' => "Is it safe to trust news simply because many people are sharing it?", 'a' => "No, widespread sharing does not confirm accuracy; checking the original source remains important."],
            ['q' => "How can I quickly check if a claim is genuine?", 'a' => "Searching for the same claim on an established news source or the relevant official website is usually the fastest reliable check."],
            ['q' => "Does this matter for exam preparation specifically?", 'a' => "Yes, relying on inaccurate current affairs information can directly affect exam performance, making verification genuinely important."],
        ],
        'conclusion' => "A few consistent fact-checking habits, especially cross-checking with reliable sources, meaningfully reduce the risk of relying on inaccurate information for both exams and everyday decisions.",
    ];

    // ---------------------------------------------------------------
    // Insert every article (published, spread over the last ~60 days)
    // ---------------------------------------------------------------
    $insArt = $pdo->prepare(
        'INSERT IGNORE INTO articles(category_id,title,slug,excerpt,content_html,thumbnail_url,status,published_at)
         VALUES(?,?,?,?,?,?,\'published\',?)'
    );

    foreach ($articles as $i => $a) {
        if (!isset($catId[$a['category']])) {
            continue;
        }

        $thumb = inforova_image($a['image_topic'], $a['image_index']);

        $html = inforova_build_article_html(
            $a['intro'],
            $a['quick_facts'],
            $a['sections'],
            $a['faqs'],
            $a['conclusion'],
            $thumb,
            $a['title']
        );

        $publishedAt = date('Y-m-d H:i:s', strtotime('-' . ((($i * 37) % 58) + 1) . ' days'));

        $insArt->execute([
            $catId[$a['category']],
            $a['title'],
            $a['slug'],
            $a['excerpt'],
            $html,
            $thumb,
            $publishedAt,
        ]);
    }

    // ---------------------------------------------------------------
    // Hub pages - a page containing organized links into many articles,
    // for extra navigational depth ("pages within pages").
    // ---------------------------------------------------------------
    $insPage = $pdo->prepare('INSERT IGNORE INTO pages(title,slug,content_html) VALUES(?,?,?)');

    $hubLink = static function (string $slug, string $title, string $desc): string {
        return '<a class="hub-link" href="' . article_url($slug) . '"><span class="hub-link-title">' . $title . '</span><span class="hub-link-desc">' . $desc . '</span></a>';
    };

    $hubGroup = static function (string $heading, string $links): string {
        return '<section class="hub-group"><h2>' . $heading . '</h2><div class="hub-grid">' . $links . '</div></section>';
    };

    $studyHub = '<p>A single starting point for every study-related guide on INFOROVA, grouped by topic so you can jump straight to what you need.</p>'
        . $hubGroup('Exam Preparation', 
            $hubLink('practical-weekly-exam-preparation-plan', 'Weekly Exam Preparation Plan', 'A practical starting plan for structuring your week.')
            . $hubLink('active-recall-vs-passive-reading', 'Active Recall vs Passive Reading', 'Why testing yourself beats re-reading.')
            . $hubLink('time-management-competitive-exam-preparation', 'Time Management for Exams', 'Dividing limited time across subjects.')
            . $hubLink('analyze-previous-year-question-papers', 'Analyzing Previous Year Papers', 'Getting more value from old papers.')
            . $hubLink('distraction-free-study-space-at-home', 'Distraction-Free Study Space', 'Simple changes that improve focus.'))
        . $hubGroup('Study Techniques',
            $hubLink('pomodoro-technique-beginners-guide', 'The Pomodoro Technique', 'Short, focused study intervals.')
            . $hubLink('cornell-method-effective-notes', 'The Cornell Note-Taking Method', 'Notes that make revision faster.')
            . $hubLink('memory-techniques-mnemonics-spaced-repetition', 'Memory Techniques', 'Mnemonics, visualization and spaced repetition.')
            . $hubLink('build-realistic-daily-study-routine', 'Building a Daily Routine', 'A schedule you can actually follow.')
            . $hubLink('stay-motivated-long-exam-preparation', 'Staying Motivated', 'Sustaining focus over months, not days.'))
        . $hubGroup('Current Affairs',
            $hubLink('daily-current-affairs-reading-habit', 'Daily Reading Habit', 'A sustainable way to follow the news.')
            . $hubLink('notes-from-newspapers-for-exams', 'Notes From Newspapers', 'Turning reading into exam-ready notes.')
            . $hubLink('static-gk-vs-current-affairs', 'Static GK vs Current Affairs', 'Understanding the difference.')
            . $hubLink('revise-year-current-affairs-before-exam', 'Revising a Full Year', 'Condensing months of notes before an exam.'));

    $jobHub = '<p>Everything on INFOROVA related to government job applications and results, organized in one place.</p>'
        . $hubGroup('Applying for a Government Job',
            $hubLink('how-to-read-a-government-job-notice', 'Reading a Job Notice Correctly', 'The starting point for any application.')
            . $hubLink('common-mistakes-government-job-applications', 'Common Application Mistakes', 'Avoidable errors that cause rejection.')
            . $hubLink('document-checklist-government-job-application', 'Document Checklist', 'What to prepare in advance.')
            . $hubLink('resume-for-government-job-applications', 'Preparing a Strong Resume', 'What verification stages look for.')
            . $hubLink('government-job-categories-pay-scale-basics', 'Job Categories and Pay Scales', 'Understanding groups and pay levels.'))
        . $hubGroup('Exam News and Results',
            $hubLink('exam-news-what-to-check-before-applying', 'What to Check Before Applying', 'Reading an exam notice properly.')
            . $hubLink('exam-admit-card-common-issues', 'Admit Card Issues', 'Common problems and fixes.')
            . $hubLink('how-to-track-exam-results', 'Tracking Results Reliably', 'Avoiding fake result pages.')
            . $hubLink('how-merit-lists-are-prepared', 'How Merit Lists Are Prepared', 'Understanding ranking and cut-offs.')
            . $hubLink('what-to-do-after-exam-result', 'After Your Result Is Published', 'A short post-result checklist.'));

    $careerHub = '<p>Guides for scholarships, career decisions and workplace skills, brought together in one resource hub.</p>'
        . $hubGroup('Scholarships',
            $hubLink('types-of-scholarships-explained', 'Types of Scholarships Explained', 'Merit, need-based and government schemes.')
            . $hubLink('strong-scholarship-application-essay', 'Writing a Strong Essay', 'What scholarship committees look for.')
            . $hubLink('common-scholarship-application-mistakes', 'Common Mistakes to Avoid', 'Why strong applications get rejected.')
            . $hubLink('scholarship-deadlines-documents-timeline', 'Deadlines and Documents Timeline', 'Staying ready for short windows.'))
        . $hubGroup('Career and Skills',
            $hubLink('choose-right-career-path-after-graduation', 'Choosing the Right Career Path', 'A framework for comparing options.')
            . $hubLink('interview-preparation-common-questions', 'Interview Preparation', 'Common questions and how to answer.')
            . $hubLink('professional-resume-online-profile', 'Resume and Online Profile', 'Keeping both consistent.')
            . $hubLink('essential-digital-skills-job-market', 'Essential Digital Skills', 'What most employers now expect.')
            . $hubLink('improve-communication-skills-interviews-work', 'Improving Communication Skills', 'Practical, learnable habits.'));

    $insPage->execute(['Complete Study Resources Hub', 'study-resources-hub', $studyHub]);
    $insPage->execute(['Government Job Complete Guide Hub', 'sarkari-job-hub', $jobHub]);
    $insPage->execute(['Career, Scholarships and Skills Hub', 'career-scholarship-hub', $careerHub]);

    // ---------------------------------------------------------------
    // Mark this expansion as done, so it never runs again.
    // ---------------------------------------------------------------
    $pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(\'content_expansion_v1\',\'done\')
        ON DUPLICATE KEY UPDATE setting_value=\'done\'')->execute();
}
