<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = $this->getCourseData();

        foreach ($courses as $courseData) {
            $instructor = User::where('email', $courseData['instructor_email'])->first();
            $category = Category::where('slug', $courseData['category_slug'])->first();

            if (!$instructor || !$category) {
                continue;
            }

            $course = Course::updateOrCreate(
                ['slug' => Str::slug($courseData['title'])],
                [
                    'instructor_id' => $instructor->id,
                    'category_id' => $category->id,
                    'title' => $courseData['title'],
                    'short_description' => $courseData['short_description'],
                    'description' => $courseData['description'],
                    'price' => $courseData['price'],
                    'original_price' => $courseData['original_price'],
                    'level' => $courseData['level'],
                    'language' => 'Arabic & English',
                    'is_bundle' => $courseData['is_bundle'],
                    'is_published' => true,
                    'is_featured' => $courseData['is_featured'],
                    'requirements' => $courseData['requirements'],
                    'learning_outcomes' => $courseData['learning_outcomes'],
                    'tags' => $courseData['tags'],
                ]
            );

            $this->createModulesAndLessons($course, $courseData['modules']);
        }
    }

    private function createModulesAndLessons(Course $course, array $modules): void
    {
        foreach ($modules as $moduleIndex => $moduleData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleData['title']],
                ['sort_order' => $moduleIndex + 1]
            );

            foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                $lesson = Lesson::updateOrCreate(
                    ['module_id' => $module->id, 'title' => $lessonData['title']],
                    [
                        'type' => $lessonData['type'],
                        'duration_minutes' => $lessonData['duration_minutes'] ?? 0,
                        'video_url' => $lessonData['type'] === 'video' ? 'https://vimeo.com/example/' . Str::slug($lessonData['title']) : null,
                        'content' => $lessonData['content'] ?? null,
                        'is_free' => $lessonData['is_free'] ?? false,
                        'sort_order' => $lessonIndex + 1,
                    ]
                );

                // If this is a quiz lesson, create the quiz
                if ($lessonData['type'] === 'quiz' && isset($moduleData['quiz'])) {
                    $this->createQuiz($course, $lesson, $moduleData['quiz']);
                }
            }
        }
    }

    private function createQuiz(Course $course, Lesson $lesson, array $quizData): void
    {
        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id, 'lesson_id' => $lesson->id],
            [
                'title' => $quizData['title'],
                'passing_score' => 70,
                'time_limit_minutes' => 15,
                'max_attempts' => 3,
            ]
        );

        foreach ($quizData['questions'] as $qIndex => $qData) {
            $question = QuizQuestion::updateOrCreate(
                ['quiz_id' => $quiz->id, 'question_text' => $qData['question']],
                [
                    'explanation' => $qData['explanation'],
                    'sort_order' => $qIndex + 1,
                ]
            );

            foreach ($qData['options'] as $optData) {
                QuizOption::updateOrCreate(
                    ['question_id' => $question->id, 'option_label' => $optData['label']],
                    [
                        'option_text' => $optData['text'],
                        'is_correct' => $optData['is_correct'],
                    ]
                );
            }
        }
    }

    private function getCourseData(): array
    {
        return [
            // Course 1
            [
                'title' => 'Advanced Clinical Cases: Internal Medicine',
                'instructor_email' => 'ahmed@spc-academy.com',
                'category_slug' => 'internal-medicine',
                'short_description' => 'Master complex internal medicine cases through systematic clinical reasoning and evidence-based approaches.',
                'description' => 'This comprehensive course covers advanced clinical cases in internal medicine, focusing on diagnostic reasoning, differential diagnosis, and management strategies. Learn from real-world scenarios presented by Dr. Ahmed Hassan with over 15 years of clinical experience.',
                'price' => 2000,
                'original_price' => 2600,
                'level' => 'advanced',
                'is_bundle' => false,
                'is_featured' => true,
                'requirements' => ['Basic knowledge of internal medicine', 'Understanding of clinical examination', 'Familiarity with common lab investigations'],
                'learning_outcomes' => ['Analyze complex clinical cases systematically', 'Develop differential diagnosis skills', 'Apply evidence-based management strategies', 'Prepare for board examinations'],
                'tags' => ['internal medicine', 'clinical cases', 'diagnosis', 'board prep'],
                'modules' => [
                    [
                        'title' => 'Approach to the Febrile Patient',
                        'lessons' => [
                            ['title' => 'Introduction to Fever of Unknown Origin', 'type' => 'video', 'duration_minutes' => 30, 'is_free' => true],
                            ['title' => 'Systematic Approach to FUO', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Infectious vs Non-Infectious Causes', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'FUO Case Studies Reading', 'type' => 'reading', 'content' => 'Detailed case studies exploring fever of unknown origin in clinical practice.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Approach to the Febrile Patient Quiz',
                            'questions' => [
                                ['question' => 'What is the classic definition of Fever of Unknown Origin (FUO)?', 'explanation' => 'Classic FUO is defined as temperature >38.3°C on several occasions, lasting >3 weeks, with no diagnosis after 1 week of inpatient investigation.', 'options' => [['label' => 'A', 'text' => 'Temperature >38.3°C for >3 weeks with no diagnosis after 1 week of investigation', 'is_correct' => true], ['label' => 'B', 'text' => 'Any fever lasting more than 24 hours', 'is_correct' => false], ['label' => 'C', 'text' => 'Temperature >37.5°C for >1 week', 'is_correct' => false], ['label' => 'D', 'text' => 'Recurrent fever episodes over 6 months', 'is_correct' => false]]],
                                ['question' => 'Which of the following is the most common category of FUO in adults?', 'explanation' => 'Infections remain the most common cause of FUO, accounting for approximately 20-30% of cases.', 'options' => [['label' => 'A', 'text' => 'Autoimmune diseases', 'is_correct' => false], ['label' => 'B', 'text' => 'Infections', 'is_correct' => true], ['label' => 'C', 'text' => 'Malignancies', 'is_correct' => false], ['label' => 'D', 'text' => 'Drug-induced fever', 'is_correct' => false]]],
                                ['question' => 'Which investigation is most useful as an initial workup for FUO?', 'explanation' => 'A thorough CBC with differential, ESR, and CRP along with blood cultures form the cornerstone of initial FUO workup.', 'options' => [['label' => 'A', 'text' => 'PET-CT scan', 'is_correct' => false], ['label' => 'B', 'text' => 'Bone marrow biopsy', 'is_correct' => false], ['label' => 'C', 'text' => 'CBC, ESR, CRP, and blood cultures', 'is_correct' => true], ['label' => 'D', 'text' => 'Liver biopsy', 'is_correct' => false]]],
                                ['question' => 'Drug-induced fever typically resolves within what timeframe after discontinuation?', 'explanation' => 'Drug fever usually resolves within 48-72 hours after the offending agent is discontinued.', 'options' => [['label' => 'A', 'text' => '2-4 hours', 'is_correct' => false], ['label' => 'B', 'text' => '48-72 hours', 'is_correct' => true], ['label' => 'C', 'text' => '1-2 weeks', 'is_correct' => false], ['label' => 'D', 'text' => '4-6 weeks', 'is_correct' => false]]],
                                ['question' => 'Which of the following malignancies is most commonly associated with FUO?', 'explanation' => 'Lymphoma, particularly Non-Hodgkin lymphoma, is the malignancy most commonly associated with FUO.', 'options' => [['label' => 'A', 'text' => 'Breast cancer', 'is_correct' => false], ['label' => 'B', 'text' => 'Colon cancer', 'is_correct' => false], ['label' => 'C', 'text' => 'Lymphoma', 'is_correct' => true], ['label' => 'D', 'text' => 'Prostate cancer', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Cardiovascular Cases in Internal Medicine',
                        'lessons' => [
                            ['title' => 'Approach to Chest Pain', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Heart Failure Case Analysis', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Infective Endocarditis Workup', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Cardiovascular Cases Quiz',
                            'questions' => [
                                ['question' => 'What is the most sensitive cardiac biomarker for acute myocardial infarction?', 'explanation' => 'High-sensitivity troponin is the most sensitive and specific biomarker for myocardial injury and is the preferred marker for diagnosing AMI.', 'options' => [['label' => 'A', 'text' => 'CK-MB', 'is_correct' => false], ['label' => 'B', 'text' => 'High-sensitivity troponin', 'is_correct' => true], ['label' => 'C', 'text' => 'Myoglobin', 'is_correct' => false], ['label' => 'D', 'text' => 'LDH', 'is_correct' => false]]],
                                ['question' => 'Which murmur is classically associated with mitral valve prolapse?', 'explanation' => 'MVP classically presents with a mid-systolic click followed by a late systolic murmur, best heard at the apex.', 'options' => [['label' => 'A', 'text' => 'Early diastolic murmur', 'is_correct' => false], ['label' => 'B', 'text' => 'Mid-systolic click with late systolic murmur', 'is_correct' => true], ['label' => 'C', 'text' => 'Continuous machinery murmur', 'is_correct' => false], ['label' => 'D', 'text' => 'Opening snap with mid-diastolic rumble', 'is_correct' => false]]],
                                ['question' => 'What is the Duke criteria used to diagnose?', 'explanation' => 'The modified Duke criteria is the standard diagnostic framework for infective endocarditis, using major and minor criteria.', 'options' => [['label' => 'A', 'text' => 'Rheumatic heart disease', 'is_correct' => false], ['label' => 'B', 'text' => 'Infective endocarditis', 'is_correct' => true], ['label' => 'C', 'text' => 'Pericarditis', 'is_correct' => false], ['label' => 'D', 'text' => 'Myocarditis', 'is_correct' => false]]],
                                ['question' => 'What is the first-line treatment for acute decompensated heart failure with volume overload?', 'explanation' => 'IV loop diuretics (furosemide) are the first-line treatment for acute decompensated heart failure with signs of congestion.', 'options' => [['label' => 'A', 'text' => 'Beta-blockers', 'is_correct' => false], ['label' => 'B', 'text' => 'IV loop diuretics', 'is_correct' => true], ['label' => 'C', 'text' => 'Calcium channel blockers', 'is_correct' => false], ['label' => 'D', 'text' => 'Digoxin', 'is_correct' => false]]],
                                ['question' => 'Which BNP level strongly suggests heart failure?', 'explanation' => 'BNP >400 pg/mL strongly suggests heart failure, while levels <100 pg/mL make HF unlikely.', 'options' => [['label' => 'A', 'text' => '<50 pg/mL', 'is_correct' => false], ['label' => 'B', 'text' => '50-100 pg/mL', 'is_correct' => false], ['label' => 'C', 'text' => '100-200 pg/mL', 'is_correct' => false], ['label' => 'D', 'text' => '>400 pg/mL', 'is_correct' => true]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Endocrine and Metabolic Disorders',
                        'lessons' => [
                            ['title' => 'Thyroid Storm vs Myxedema Coma', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Diabetic Emergencies Management', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Adrenal Crisis Recognition', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Electrolyte Imbalances Review', 'type' => 'reading', 'content' => 'Comprehensive review of electrolyte disorders including hyponatremia, hyperkalemia, and hypercalcemia.'],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Endocrine and Metabolic Disorders Quiz',
                            'questions' => [
                                ['question' => 'What is the hallmark triad of diabetic ketoacidosis?', 'explanation' => 'DKA is characterized by hyperglycemia (>250 mg/dL), metabolic acidosis (pH <7.3), and ketonemia/ketonuria.', 'options' => [['label' => 'A', 'text' => 'Hyperglycemia, metabolic acidosis, and ketonemia', 'is_correct' => true], ['label' => 'B', 'text' => 'Hypoglycemia, respiratory alkalosis, and ketonemia', 'is_correct' => false], ['label' => 'C', 'text' => 'Hyperglycemia, metabolic alkalosis, and glycosuria', 'is_correct' => false], ['label' => 'D', 'text' => 'Hypoglycemia, metabolic acidosis, and proteinuria', 'is_correct' => false]]],
                                ['question' => 'What is the initial treatment priority in DKA?', 'explanation' => 'IV fluid resuscitation with normal saline is the first priority to restore intravascular volume and improve perfusion.', 'options' => [['label' => 'A', 'text' => 'Insulin bolus', 'is_correct' => false], ['label' => 'B', 'text' => 'IV fluid resuscitation', 'is_correct' => true], ['label' => 'C', 'text' => 'Bicarbonate infusion', 'is_correct' => false], ['label' => 'D', 'text' => 'Potassium replacement', 'is_correct' => false]]],
                                ['question' => 'Which lab finding differentiates thyroid storm from uncomplicated thyrotoxicosis?', 'explanation' => 'Thyroid storm is a clinical diagnosis. The Burch-Wartofsky score uses fever, CNS effects, GI-hepatic dysfunction, and tachycardia to differentiate it from uncomplicated thyrotoxicosis.', 'options' => [['label' => 'A', 'text' => 'Higher T4 levels', 'is_correct' => false], ['label' => 'B', 'text' => 'It is a clinical diagnosis based on Burch-Wartofsky score', 'is_correct' => true], ['label' => 'C', 'text' => 'Presence of TSH receptor antibodies', 'is_correct' => false], ['label' => 'D', 'text' => 'Elevated TSH levels', 'is_correct' => false]]],
                                ['question' => 'What is the most common electrolyte abnormality in hospitalized patients?', 'explanation' => 'Hyponatremia is the most common electrolyte disturbance seen in hospitalized patients, affecting up to 30% of inpatients.', 'options' => [['label' => 'A', 'text' => 'Hyperkalemia', 'is_correct' => false], ['label' => 'B', 'text' => 'Hypocalcemia', 'is_correct' => false], ['label' => 'C', 'text' => 'Hyponatremia', 'is_correct' => true], ['label' => 'D', 'text' => 'Hypomagnesemia', 'is_correct' => false]]],
                                ['question' => 'What is the emergency treatment for adrenal crisis?', 'explanation' => 'IV hydrocortisone (100mg bolus then 50mg Q8H) along with aggressive IV fluid resuscitation is the standard emergency treatment for adrenal crisis.', 'options' => [['label' => 'A', 'text' => 'Oral prednisone 5mg', 'is_correct' => false], ['label' => 'B', 'text' => 'IV dexamethasone only', 'is_correct' => false], ['label' => 'C', 'text' => 'IV hydrocortisone and IV fluids', 'is_correct' => true], ['label' => 'D', 'text' => 'Fludrocortisone only', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Renal and Respiratory Cases',
                        'lessons' => [
                            ['title' => 'Acute Kidney Injury Classification', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Community-Acquired Pneumonia', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Pulmonary Embolism Diagnosis', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Module 4 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Renal and Respiratory Cases Quiz',
                            'questions' => [
                                ['question' => 'Which classification system is used for staging Acute Kidney Injury?', 'explanation' => 'The KDIGO criteria (based on RIFLE and AKIN) is the current standard for AKI staging using creatinine rise and urine output.', 'options' => [['label' => 'A', 'text' => 'NYHA classification', 'is_correct' => false], ['label' => 'B', 'text' => 'KDIGO criteria', 'is_correct' => true], ['label' => 'C', 'text' => 'CURB-65 score', 'is_correct' => false], ['label' => 'D', 'text' => 'Child-Pugh score', 'is_correct' => false]]],
                                ['question' => 'What is the Wells score used to assess?', 'explanation' => 'The Wells score is a clinical prediction rule used to estimate the pre-test probability of pulmonary embolism.', 'options' => [['label' => 'A', 'text' => 'Heart failure severity', 'is_correct' => false], ['label' => 'B', 'text' => 'Pre-test probability of pulmonary embolism', 'is_correct' => true], ['label' => 'C', 'text' => 'Pneumonia severity', 'is_correct' => false], ['label' => 'D', 'text' => 'Renal failure prognosis', 'is_correct' => false]]],
                                ['question' => 'Which antibiotic regimen is first-line for outpatient CAP without comorbidities?', 'explanation' => 'Amoxicillin or doxycycline is recommended as first-line for low-risk outpatient CAP without comorbidities per ATS/IDSA guidelines.', 'options' => [['label' => 'A', 'text' => 'IV vancomycin', 'is_correct' => false], ['label' => 'B', 'text' => 'Amoxicillin or doxycycline', 'is_correct' => true], ['label' => 'C', 'text' => 'Meropenem', 'is_correct' => false], ['label' => 'D', 'text' => 'Ciprofloxacin', 'is_correct' => false]]],
                                ['question' => 'What is the fractional excretion of sodium (FeNa) in prerenal AKI?', 'explanation' => 'FeNa <1% suggests prerenal AKI where the kidneys are appropriately retaining sodium due to decreased perfusion.', 'options' => [['label' => 'A', 'text' => '<1%', 'is_correct' => true], ['label' => 'B', 'text' => '1-2%', 'is_correct' => false], ['label' => 'C', 'text' => '>2%', 'is_correct' => false], ['label' => 'D', 'text' => '>5%', 'is_correct' => false]]],
                                ['question' => 'What is the gold standard for diagnosing pulmonary embolism?', 'explanation' => 'CT Pulmonary Angiography (CTPA) is the gold standard imaging for diagnosing pulmonary embolism due to high sensitivity and specificity.', 'options' => [['label' => 'A', 'text' => 'Chest X-ray', 'is_correct' => false], ['label' => 'B', 'text' => 'D-dimer assay', 'is_correct' => false], ['label' => 'C', 'text' => 'CT pulmonary angiography', 'is_correct' => true], ['label' => 'D', 'text' => 'Ventilation-perfusion scan', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 2
            [
                'title' => 'Pediatric Advanced Life Support (PALS) Prep',
                'instructor_email' => 'mona@spc-academy.com',
                'category_slug' => 'pediatrics',
                'short_description' => 'Comprehensive PALS preparation covering pediatric emergencies, algorithms, and hands-on scenario-based learning.',
                'description' => 'Prepare for your PALS certification with this in-depth course by Dr. Mona Ibrahim. Covers all PALS algorithms, pediatric assessment, and emergency management with interactive case scenarios.',
                'price' => 1500,
                'original_price' => 1950,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => true,
                'requirements' => ['Basic Life Support (BLS) certification', 'Basic pediatric knowledge', 'Clinical experience preferred'],
                'learning_outcomes' => ['Master PALS algorithms', 'Recognize pediatric emergencies', 'Apply systematic assessment approach', 'Manage pediatric cardiac arrest'],
                'tags' => ['PALS', 'pediatrics', 'emergency', 'life support', 'certification'],
                'modules' => [
                    [
                        'title' => 'Pediatric Assessment Approach',
                        'lessons' => [
                            ['title' => 'Introduction to PALS', 'type' => 'video', 'duration_minutes' => 25, 'is_free' => true],
                            ['title' => 'Pediatric Assessment Triangle', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Primary and Secondary Assessment', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Pediatric Assessment Quiz',
                            'questions' => [
                                ['question' => 'What are the three components of the Pediatric Assessment Triangle (PAT)?', 'explanation' => 'The PAT consists of Appearance (muscle tone, interactiveness, consolability, look/gaze, speech/cry), Work of Breathing, and Circulation to Skin.', 'options' => [['label' => 'A', 'text' => 'Appearance, Work of Breathing, Circulation to Skin', 'is_correct' => true], ['label' => 'B', 'text' => 'Airway, Breathing, Circulation', 'is_correct' => false], ['label' => 'C', 'text' => 'Heart rate, Blood pressure, Respiratory rate', 'is_correct' => false], ['label' => 'D', 'text' => 'Consciousness, Respiration, Perfusion', 'is_correct' => false]]],
                                ['question' => 'What is the normal heart rate range for a 1-year-old infant?', 'explanation' => 'Normal heart rate for infants aged 1 year is approximately 100-150 bpm, though it can vary with activity and state.', 'options' => [['label' => 'A', 'text' => '60-80 bpm', 'is_correct' => false], ['label' => 'B', 'text' => '80-100 bpm', 'is_correct' => false], ['label' => 'C', 'text' => '100-150 bpm', 'is_correct' => true], ['label' => 'D', 'text' => '150-200 bpm', 'is_correct' => false]]],
                                ['question' => 'Which mnemonic helps assess Appearance in the PAT?', 'explanation' => 'The TICLS mnemonic stands for Tone, Interactiveness, Consolability, Look/Gaze, and Speech/Cry.', 'options' => [['label' => 'A', 'text' => 'AVPU', 'is_correct' => false], ['label' => 'B', 'text' => 'TICLS', 'is_correct' => true], ['label' => 'C', 'text' => 'SAMPLE', 'is_correct' => false], ['label' => 'D', 'text' => 'OPQRST', 'is_correct' => false]]],
                                ['question' => 'What is the minimum systolic blood pressure for a 5-year-old child?', 'explanation' => 'Minimum acceptable systolic BP for children 1-10 years can be estimated using the formula: 70 + (2 x age in years). For a 5-year-old: 70 + 10 = 80 mmHg.', 'options' => [['label' => 'A', 'text' => '60 mmHg', 'is_correct' => false], ['label' => 'B', 'text' => '70 mmHg', 'is_correct' => false], ['label' => 'C', 'text' => '80 mmHg', 'is_correct' => true], ['label' => 'D', 'text' => '90 mmHg', 'is_correct' => false]]],
                                ['question' => 'Capillary refill time greater than how many seconds suggests poor perfusion?', 'explanation' => 'Capillary refill time >2 seconds suggests poor peripheral perfusion and may indicate shock in pediatric patients.', 'options' => [['label' => 'A', 'text' => '1 second', 'is_correct' => false], ['label' => 'B', 'text' => '2 seconds', 'is_correct' => true], ['label' => 'C', 'text' => '4 seconds', 'is_correct' => false], ['label' => 'D', 'text' => '5 seconds', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pediatric Bradycardia and Tachycardia Algorithms',
                        'lessons' => [
                            ['title' => 'Pediatric Bradycardia Algorithm', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Pediatric Tachycardia with Pulse', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'SVT vs Sinus Tachycardia', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Algorithm Flowcharts Guide', 'type' => 'reading', 'content' => 'Printable flowcharts for all PALS bradycardia and tachycardia algorithms.'],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Bradycardia and Tachycardia Quiz',
                            'questions' => [
                                ['question' => 'What is the first drug of choice for symptomatic pediatric bradycardia?', 'explanation' => 'Epinephrine is the first-line drug for symptomatic bradycardia in PALS. Atropine may be considered if increased vagal tone or primary AV block is suspected.', 'options' => [['label' => 'A', 'text' => 'Atropine', 'is_correct' => false], ['label' => 'B', 'text' => 'Epinephrine', 'is_correct' => true], ['label' => 'C', 'text' => 'Amiodarone', 'is_correct' => false], ['label' => 'D', 'text' => 'Adenosine', 'is_correct' => false]]],
                                ['question' => 'What is the dose of adenosine for SVT in pediatrics?', 'explanation' => 'The initial dose of adenosine for SVT in children is 0.1 mg/kg (max 6 mg) rapid IV push, followed by rapid saline flush.', 'options' => [['label' => 'A', 'text' => '0.01 mg/kg', 'is_correct' => false], ['label' => 'B', 'text' => '0.1 mg/kg', 'is_correct' => true], ['label' => 'C', 'text' => '1 mg/kg', 'is_correct' => false], ['label' => 'D', 'text' => '6 mg flat dose for all ages', 'is_correct' => false]]],
                                ['question' => 'Heart rate >220 bpm in an infant most likely suggests?', 'explanation' => 'SVT in infants typically presents with heart rates >220 bpm, while sinus tachycardia rarely exceeds 220 bpm in infants.', 'options' => [['label' => 'A', 'text' => 'Sinus tachycardia', 'is_correct' => false], ['label' => 'B', 'text' => 'Supraventricular tachycardia (SVT)', 'is_correct' => true], ['label' => 'C', 'text' => 'Ventricular tachycardia', 'is_correct' => false], ['label' => 'D', 'text' => 'Atrial fibrillation', 'is_correct' => false]]],
                                ['question' => 'What is the synchronized cardioversion dose for unstable SVT?', 'explanation' => 'The initial synchronized cardioversion dose for SVT is 0.5-1 J/kg. If ineffective, the dose can be increased to 2 J/kg.', 'options' => [['label' => 'A', 'text' => '0.5-1 J/kg', 'is_correct' => true], ['label' => 'B', 'text' => '2-4 J/kg', 'is_correct' => false], ['label' => 'C', 'text' => '5 J/kg', 'is_correct' => false], ['label' => 'D', 'text' => '10 J/kg', 'is_correct' => false]]],
                                ['question' => 'Which vagal maneuver is appropriate for an infant with SVT?', 'explanation' => 'Ice to the face (diving reflex) is the preferred vagal maneuver for infants with SVT. Carotid massage is not recommended in infants.', 'options' => [['label' => 'A', 'text' => 'Carotid sinus massage', 'is_correct' => false], ['label' => 'B', 'text' => 'Valsalva maneuver', 'is_correct' => false], ['label' => 'C', 'text' => 'Ice to the face', 'is_correct' => true], ['label' => 'D', 'text' => 'Eyeball pressure', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pediatric Cardiac Arrest Management',
                        'lessons' => [
                            ['title' => 'Pediatric Cardiac Arrest Algorithm', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Shockable vs Non-Shockable Rhythms', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Post-Cardiac Arrest Care', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Cardiac Arrest Management Quiz',
                            'questions' => [
                                ['question' => 'What is the recommended compression-to-ventilation ratio for single-rescuer infant CPR?', 'explanation' => 'For single-rescuer CPR in infants and children, the compression-to-ventilation ratio is 30:2, the same as adults.', 'options' => [['label' => 'A', 'text' => '15:2', 'is_correct' => false], ['label' => 'B', 'text' => '30:2', 'is_correct' => true], ['label' => 'C', 'text' => '5:1', 'is_correct' => false], ['label' => 'D', 'text' => '10:2', 'is_correct' => false]]],
                                ['question' => 'What is the defibrillation dose for pediatric VF/pulseless VT?', 'explanation' => 'The initial defibrillation dose in pediatric cardiac arrest is 2 J/kg, increasing to 4 J/kg for subsequent shocks.', 'options' => [['label' => 'A', 'text' => '1 J/kg', 'is_correct' => false], ['label' => 'B', 'text' => '2 J/kg initial, 4 J/kg subsequent', 'is_correct' => true], ['label' => 'C', 'text' => '5 J/kg', 'is_correct' => false], ['label' => 'D', 'text' => '200 J flat dose', 'is_correct' => false]]],
                                ['question' => 'What is the dose of epinephrine in pediatric cardiac arrest?', 'explanation' => 'Epinephrine dose in pediatric cardiac arrest is 0.01 mg/kg (0.1 mL/kg of 1:10,000) IV/IO, given every 3-5 minutes.', 'options' => [['label' => 'A', 'text' => '0.001 mg/kg', 'is_correct' => false], ['label' => 'B', 'text' => '0.01 mg/kg', 'is_correct' => true], ['label' => 'C', 'text' => '0.1 mg/kg', 'is_correct' => false], ['label' => 'D', 'text' => '1 mg flat dose', 'is_correct' => false]]],
                                ['question' => 'Which is the most common cause of cardiac arrest in children?', 'explanation' => 'Unlike adults where cardiac arrest is often primary, pediatric cardiac arrest most commonly results from progressive respiratory failure or shock.', 'options' => [['label' => 'A', 'text' => 'Primary cardiac arrhythmia', 'is_correct' => false], ['label' => 'B', 'text' => 'Respiratory failure or shock', 'is_correct' => true], ['label' => 'C', 'text' => 'Congenital heart disease', 'is_correct' => false], ['label' => 'D', 'text' => 'Electrolyte imbalance', 'is_correct' => false]]],
                                ['question' => 'What are the Hs and Ts of reversible causes of cardiac arrest?', 'explanation' => 'The Hs include Hypovolemia, Hypoxia, Hydrogen ion (acidosis), Hypo/Hyperkalemia, Hypothermia, Hypoglycemia. The Ts include Tension pneumothorax, Tamponade, Toxins, Thrombosis.', 'options' => [['label' => 'A', 'text' => 'Hypovolemia, Hypoxia, Hydrogen ion, Hypo/Hyperkalemia, Hypothermia; Tension pneumothorax, Tamponade, Toxins, Thrombosis', 'is_correct' => true], ['label' => 'B', 'text' => 'Hemorrhage, Hypotension, Hyperglycemia; Trauma, Tachycardia, Tumor', 'is_correct' => false], ['label' => 'C', 'text' => 'Heart failure, Hyperthermia; Tonsillitis, Thrombocytopenia', 'is_correct' => false], ['label' => 'D', 'text' => 'Hepatitis, Hydrocephalus; Tetanus, Tuberculosis', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 3
            [
                'title' => 'ECG Interpretation Masterclass',
                'instructor_email' => 'khaled@spc-academy.com',
                'category_slug' => 'cardiology',
                'short_description' => 'Learn systematic ECG interpretation from basic principles to complex arrhythmias with Dr. Khaled Mostafa.',
                'description' => 'A complete ECG interpretation course covering normal and abnormal ECG patterns, arrhythmia recognition, and clinical correlation. Includes hundreds of practice ECG strips for self-assessment.',
                'price' => 1800,
                'original_price' => 2340,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Basic anatomy of the heart', 'Understanding of cardiac physiology', 'Clinical experience helpful but not required'],
                'learning_outcomes' => ['Read ECGs systematically', 'Identify common arrhythmias', 'Recognize ST-segment changes', 'Correlate ECG findings with clinical presentation'],
                'tags' => ['ECG', 'cardiology', 'arrhythmia', 'electrocardiogram'],
                'modules' => [
                    [
                        'title' => 'Basic ECG Principles',
                        'lessons' => [
                            ['title' => 'Introduction to ECG', 'type' => 'video', 'duration_minutes' => 25, 'is_free' => true],
                            ['title' => 'ECG Lead Placement and Anatomy', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Normal ECG Waveforms', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'ECG Reading Guide', 'type' => 'reading', 'content' => 'Step-by-step guide to systematic ECG interpretation including rate, rhythm, axis, intervals, and morphology.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Basic ECG Principles Quiz',
                            'questions' => [
                                ['question' => 'What does the P wave represent?', 'explanation' => 'The P wave represents atrial depolarization, which is the electrical activation spreading through the atria before atrial contraction.', 'options' => [['label' => 'A', 'text' => 'Atrial depolarization', 'is_correct' => true], ['label' => 'B', 'text' => 'Ventricular depolarization', 'is_correct' => false], ['label' => 'C', 'text' => 'Ventricular repolarization', 'is_correct' => false], ['label' => 'D', 'text' => 'Atrial repolarization', 'is_correct' => false]]],
                                ['question' => 'What is the normal PR interval duration?', 'explanation' => 'The normal PR interval ranges from 0.12 to 0.20 seconds (120-200 ms), representing conduction through the AV node.', 'options' => [['label' => 'A', 'text' => '0.06-0.10 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '0.12-0.20 seconds', 'is_correct' => true], ['label' => 'C', 'text' => '0.24-0.32 seconds', 'is_correct' => false], ['label' => 'D', 'text' => '0.36-0.44 seconds', 'is_correct' => false]]],
                                ['question' => 'Which ECG lead looks directly at the left ventricle lateral wall?', 'explanation' => 'Leads I, aVL, V5, and V6 are the lateral leads that look at the lateral wall of the left ventricle.', 'options' => [['label' => 'A', 'text' => 'Lead II', 'is_correct' => false], ['label' => 'B', 'text' => 'Lead aVR', 'is_correct' => false], ['label' => 'C', 'text' => 'Lead V1', 'is_correct' => false], ['label' => 'D', 'text' => 'Lead V5', 'is_correct' => true]]],
                                ['question' => 'How do you calculate heart rate from an ECG with regular rhythm?', 'explanation' => 'For regular rhythms, divide 300 by the number of large boxes between two consecutive R waves (300/RR interval in large boxes).', 'options' => [['label' => 'A', 'text' => '300 divided by number of large boxes between R waves', 'is_correct' => true], ['label' => 'B', 'text' => 'Count the number of P waves in 6 seconds and multiply by 10', 'is_correct' => false], ['label' => 'C', 'text' => '600 divided by number of small boxes', 'is_correct' => false], ['label' => 'D', 'text' => 'Multiply the number of QRS complexes by 100', 'is_correct' => false]]],
                                ['question' => 'What does the QRS complex represent?', 'explanation' => 'The QRS complex represents ventricular depolarization - the electrical activation of both ventricles before ventricular contraction.', 'options' => [['label' => 'A', 'text' => 'Atrial depolarization', 'is_correct' => false], ['label' => 'B', 'text' => 'Atrial repolarization', 'is_correct' => false], ['label' => 'C', 'text' => 'Ventricular depolarization', 'is_correct' => true], ['label' => 'D', 'text' => 'Ventricular repolarization', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Atrial Arrhythmias',
                        'lessons' => [
                            ['title' => 'Atrial Fibrillation and Flutter', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Supraventricular Tachycardia', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Atrial Enlargement Patterns', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Atrial Arrhythmias Quiz',
                            'questions' => [
                                ['question' => 'What is the characteristic ECG finding in atrial fibrillation?', 'explanation' => 'Atrial fibrillation shows an irregularly irregular rhythm with absent P waves, replaced by fibrillatory baseline.', 'options' => [['label' => 'A', 'text' => 'Regular rhythm with saw-tooth pattern', 'is_correct' => false], ['label' => 'B', 'text' => 'Irregularly irregular rhythm with absent P waves', 'is_correct' => true], ['label' => 'C', 'text' => 'Regular narrow complex tachycardia', 'is_correct' => false], ['label' => 'D', 'text' => 'Wide QRS complexes with AV dissociation', 'is_correct' => false]]],
                                ['question' => 'What is the typical atrial rate in atrial flutter?', 'explanation' => 'Atrial flutter has a characteristic atrial rate of approximately 300 bpm with saw-tooth flutter waves, commonly with 2:1 block giving ventricular rate of 150 bpm.', 'options' => [['label' => 'A', 'text' => '100-150 bpm', 'is_correct' => false], ['label' => 'B', 'text' => '150-200 bpm', 'is_correct' => false], ['label' => 'C', 'text' => 'Approximately 300 bpm', 'is_correct' => true], ['label' => 'D', 'text' => '>500 bpm', 'is_correct' => false]]],
                                ['question' => 'Which scoring system assesses stroke risk in atrial fibrillation?', 'explanation' => 'The CHA2DS2-VASc score assesses stroke risk in non-valvular atrial fibrillation to guide anticoagulation therapy.', 'options' => [['label' => 'A', 'text' => 'HEART score', 'is_correct' => false], ['label' => 'B', 'text' => 'CHA2DS2-VASc score', 'is_correct' => true], ['label' => 'C', 'text' => 'Wells score', 'is_correct' => false], ['label' => 'D', 'text' => 'GRACE score', 'is_correct' => false]]],
                                ['question' => 'What distinguishes multifocal atrial tachycardia (MAT) on ECG?', 'explanation' => 'MAT shows at least 3 different P-wave morphologies, varying PP intervals, and varying PR intervals with rate >100 bpm.', 'options' => [['label' => 'A', 'text' => 'Uniform P waves with regular rhythm', 'is_correct' => false], ['label' => 'B', 'text' => 'At least 3 different P-wave morphologies with irregular rhythm', 'is_correct' => true], ['label' => 'C', 'text' => 'Saw-tooth flutter waves', 'is_correct' => false], ['label' => 'D', 'text' => 'Absent P waves with wide QRS', 'is_correct' => false]]],
                                ['question' => 'P mitrale on ECG suggests enlargement of which chamber?', 'explanation' => 'P mitrale (broad, notched P wave >0.12 sec in lead II) indicates left atrial enlargement, commonly seen in mitral valve disease.', 'options' => [['label' => 'A', 'text' => 'Right atrium', 'is_correct' => false], ['label' => 'B', 'text' => 'Left atrium', 'is_correct' => true], ['label' => 'C', 'text' => 'Right ventricle', 'is_correct' => false], ['label' => 'D', 'text' => 'Left ventricle', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Ventricular Arrhythmias and Conduction Blocks',
                        'lessons' => [
                            ['title' => 'Ventricular Tachycardia and Fibrillation', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'AV Blocks: First, Second, and Third Degree', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Bundle Branch Blocks', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Conduction Block Summary', 'type' => 'reading', 'content' => 'Quick reference guide for identifying and differentiating AV blocks and bundle branch blocks.'],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Ventricular Arrhythmias and Conduction Blocks Quiz',
                            'questions' => [
                                ['question' => 'What is the hallmark ECG feature of third-degree (complete) heart block?', 'explanation' => 'Complete heart block shows AV dissociation where P waves and QRS complexes march independently with no relationship between them.', 'options' => [['label' => 'A', 'text' => 'Progressive PR prolongation', 'is_correct' => false], ['label' => 'B', 'text' => 'AV dissociation with independent P waves and QRS complexes', 'is_correct' => true], ['label' => 'C', 'text' => 'Grouped beating with dropped QRS', 'is_correct' => false], ['label' => 'D', 'text' => 'Constant prolonged PR interval', 'is_correct' => false]]],
                                ['question' => 'Which bundle branch block shows an RSR prime pattern in V1?', 'explanation' => 'Right bundle branch block (RBBB) classically shows an RSR\' (M-shaped) pattern in V1 with QRS >0.12 seconds.', 'options' => [['label' => 'A', 'text' => 'Left bundle branch block', 'is_correct' => false], ['label' => 'B', 'text' => 'Right bundle branch block', 'is_correct' => true], ['label' => 'C', 'text' => 'Left anterior fascicular block', 'is_correct' => false], ['label' => 'D', 'text' => 'Left posterior fascicular block', 'is_correct' => false]]],
                                ['question' => 'Mobitz Type II second-degree AV block is characterized by?', 'explanation' => 'Mobitz Type II shows constant PR intervals with sudden dropped QRS complexes, often indicating disease below the AV node (infranodal).', 'options' => [['label' => 'A', 'text' => 'Progressive PR prolongation before a dropped beat', 'is_correct' => false], ['label' => 'B', 'text' => 'Constant PR interval with sudden dropped QRS complexes', 'is_correct' => true], ['label' => 'C', 'text' => 'No P waves with regular QRS', 'is_correct' => false], ['label' => 'D', 'text' => 'Variable PR intervals with irregular QRS', 'is_correct' => false]]],
                                ['question' => 'What QRS duration defines a wide complex tachycardia?', 'explanation' => 'Wide complex tachycardia is defined as tachycardia with QRS duration >=0.12 seconds (120 ms or 3 small boxes).', 'options' => [['label' => 'A', 'text' => '>0.08 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '>0.10 seconds', 'is_correct' => false], ['label' => 'C', 'text' => '>=0.12 seconds', 'is_correct' => true], ['label' => 'D', 'text' => '>0.16 seconds', 'is_correct' => false]]],
                                ['question' => 'Torsades de Pointes is associated with which ECG finding?', 'explanation' => 'Torsades de Pointes is a polymorphic VT associated with prolonged QT interval. It shows the characteristic twisting of QRS complexes around the baseline.', 'options' => [['label' => 'A', 'text' => 'Short QT interval', 'is_correct' => false], ['label' => 'B', 'text' => 'Prolonged QT interval', 'is_correct' => true], ['label' => 'C', 'text' => 'Delta waves', 'is_correct' => false], ['label' => 'D', 'text' => 'ST elevation', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'ST-Segment Changes and Ischemia',
                        'lessons' => [
                            ['title' => 'STEMI Recognition by Territory', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'NSTEMI and Unstable Angina ECG', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'ECG Mimics of STEMI', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Module 4 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'ST-Segment Changes Quiz',
                            'questions' => [
                                ['question' => 'ST elevation in leads II, III, and aVF indicates ischemia in which coronary territory?', 'explanation' => 'Leads II, III, and aVF are the inferior leads, corresponding to the right coronary artery (RCA) territory in most patients.', 'options' => [['label' => 'A', 'text' => 'Left anterior descending artery', 'is_correct' => false], ['label' => 'B', 'text' => 'Left circumflex artery', 'is_correct' => false], ['label' => 'C', 'text' => 'Right coronary artery (inferior wall)', 'is_correct' => true], ['label' => 'D', 'text' => 'Left main coronary artery', 'is_correct' => false]]],
                                ['question' => 'Which condition can mimic STEMI on ECG?', 'explanation' => 'Early repolarization, pericarditis, LBBB, LVH, and Brugada syndrome can all produce ST elevation that mimics STEMI.', 'options' => [['label' => 'A', 'text' => 'Atrial fibrillation', 'is_correct' => false], ['label' => 'B', 'text' => 'Acute pericarditis', 'is_correct' => true], ['label' => 'C', 'text' => 'Right bundle branch block', 'is_correct' => false], ['label' => 'D', 'text' => 'Sinus bradycardia', 'is_correct' => false]]],
                                ['question' => 'Reciprocal changes on ECG in the setting of STEMI refer to?', 'explanation' => 'Reciprocal changes are ST depression seen in leads opposite to the territory of ST elevation, supporting the diagnosis of acute STEMI.', 'options' => [['label' => 'A', 'text' => 'ST elevation in adjacent leads', 'is_correct' => false], ['label' => 'B', 'text' => 'ST depression in opposite leads', 'is_correct' => true], ['label' => 'C', 'text' => 'T wave inversion in all leads', 'is_correct' => false], ['label' => 'D', 'text' => 'Q waves in the same territory', 'is_correct' => false]]],
                                ['question' => 'Wellens syndrome T-wave pattern suggests critical stenosis of which artery?', 'explanation' => 'Wellens syndrome (deeply inverted or biphasic T waves in V2-V3) indicates critical stenosis of the proximal LAD artery.', 'options' => [['label' => 'A', 'text' => 'Right coronary artery', 'is_correct' => false], ['label' => 'B', 'text' => 'Proximal left anterior descending artery', 'is_correct' => true], ['label' => 'C', 'text' => 'Left circumflex artery', 'is_correct' => false], ['label' => 'D', 'text' => 'Posterior descending artery', 'is_correct' => false]]],
                                ['question' => 'What is the significance of new Q waves on ECG?', 'explanation' => 'Pathological Q waves (>0.04 sec wide, >25% of R wave height) indicate myocardial necrosis/infarction and suggest completed transmural MI.', 'options' => [['label' => 'A', 'text' => 'Normal variant in young patients', 'is_correct' => false], ['label' => 'B', 'text' => 'They indicate myocardial necrosis or completed infarction', 'is_correct' => true], ['label' => 'C', 'text' => 'Electrolyte imbalance', 'is_correct' => false], ['label' => 'D', 'text' => 'Drug toxicity', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 4
            [
                'title' => 'Surgical Skills Fundamentals',
                'instructor_email' => 'omar@spc-academy.com',
                'category_slug' => 'surgery',
                'short_description' => 'Essential surgical skills training covering instruments, suturing, knot tying, and basic operative techniques.',
                'description' => 'A hands-on surgical skills course designed for medical students and junior residents. Dr. Omar Farouk guides you through fundamental techniques with detailed demonstrations and case-based learning.',
                'price' => 2500,
                'original_price' => 3250,
                'level' => 'beginner',
                'is_bundle' => false,
                'is_featured' => true,
                'requirements' => ['Basic anatomy knowledge', 'Medical student or above', 'Access to suture practice kit recommended'],
                'learning_outcomes' => ['Identify and handle surgical instruments correctly', 'Perform basic suturing techniques', 'Understand sterile technique principles', 'Apply wound closure methods'],
                'tags' => ['surgery', 'surgical skills', 'suturing', 'operative techniques'],
                'modules' => [
                    [
                        'title' => 'Surgical Instruments and Sterile Technique',
                        'lessons' => [
                            ['title' => 'Introduction to Surgical Instruments', 'type' => 'video', 'duration_minutes' => 25, 'is_free' => true],
                            ['title' => 'Handling and Passing Instruments', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Principles of Sterile Technique', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Scrubbing, Gowning, and Gloving', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Instrument Identification Guide', 'type' => 'reading', 'content' => 'Illustrated guide to common surgical instruments used in general surgery.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Surgical Instruments and Sterile Technique Quiz',
                            'questions' => [
                                ['question' => 'Which instrument is used for grasping tissue during dissection?', 'explanation' => 'Tissue forceps (toothed forceps) are used for grasping and holding tissue during dissection, providing better grip than smooth forceps.', 'options' => [['label' => 'A', 'text' => 'Kocher clamp', 'is_correct' => false], ['label' => 'B', 'text' => 'Tissue forceps', 'is_correct' => true], ['label' => 'C', 'text' => 'Needle holder', 'is_correct' => false], ['label' => 'D', 'text' => 'Retractor', 'is_correct' => false]]],
                                ['question' => 'What is the minimum scrub time recommended for surgical hand antisepsis?', 'explanation' => 'The traditional timed surgical scrub requires a minimum of 2-5 minutes depending on the antiseptic agent used (e.g., 3-5 minutes for povidone-iodine).', 'options' => [['label' => 'A', 'text' => '30 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '1 minute', 'is_correct' => false], ['label' => 'C', 'text' => '3-5 minutes', 'is_correct' => true], ['label' => 'D', 'text' => '10 minutes', 'is_correct' => false]]],
                                ['question' => 'Which of the following breaks sterile technique?', 'explanation' => 'Reaching over a sterile field is a clear breach of sterile technique as it risks contamination from non-sterile areas above.', 'options' => [['label' => 'A', 'text' => 'Keeping hands above waist level', 'is_correct' => false], ['label' => 'B', 'text' => 'Passing items within the sterile field', 'is_correct' => false], ['label' => 'C', 'text' => 'Reaching over the sterile field', 'is_correct' => true], ['label' => 'D', 'text' => 'Opening sterile packages away from the body', 'is_correct' => false]]],
                                ['question' => 'A Mayo scissors is primarily used for?', 'explanation' => 'Mayo scissors (curved or straight) are heavy scissors primarily used for cutting sutures and heavy tissues like fascia.', 'options' => [['label' => 'A', 'text' => 'Delicate tissue dissection', 'is_correct' => false], ['label' => 'B', 'text' => 'Cutting sutures and heavy tissue', 'is_correct' => true], ['label' => 'C', 'text' => 'Grasping blood vessels', 'is_correct' => false], ['label' => 'D', 'text' => 'Skin retraction', 'is_correct' => false]]],
                                ['question' => 'Which glove size is determined during gloving if the glove fits too tight?', 'explanation' => 'If a surgical glove is too tight, you should go up by half a size. Tight gloves impair dexterity and increase the risk of tearing during procedures.', 'options' => [['label' => 'A', 'text' => 'Use the same size with double gloving', 'is_correct' => false], ['label' => 'B', 'text' => 'Go up by half a size', 'is_correct' => true], ['label' => 'C', 'text' => 'Go up by a full size', 'is_correct' => false], ['label' => 'D', 'text' => 'Stretch the glove before wearing', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Suturing Techniques',
                        'lessons' => [
                            ['title' => 'Simple Interrupted Sutures', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Continuous Sutures and Running Stitches', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Mattress Sutures: Horizontal and Vertical', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Subcuticular Suturing', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Suturing Techniques Quiz',
                            'questions' => [
                                ['question' => 'Which suture technique provides the best wound eversion?', 'explanation' => 'Vertical mattress sutures provide excellent wound eversion and are useful for thick skin or areas under tension.', 'options' => [['label' => 'A', 'text' => 'Simple interrupted', 'is_correct' => false], ['label' => 'B', 'text' => 'Simple continuous', 'is_correct' => false], ['label' => 'C', 'text' => 'Vertical mattress', 'is_correct' => true], ['label' => 'D', 'text' => 'Subcuticular', 'is_correct' => false]]],
                                ['question' => 'What is the recommended needle holder grip?', 'explanation' => 'The palmed grip (thenar eminence grip) provides the best control. The needle holder should be held with the thumb and ring finger in the rings.', 'options' => [['label' => 'A', 'text' => 'Pencil grip', 'is_correct' => false], ['label' => 'B', 'text' => 'Thenar/palmed grip with thumb and ring finger in rings', 'is_correct' => true], ['label' => 'C', 'text' => 'Full hand grip', 'is_correct' => false], ['label' => 'D', 'text' => 'Index finger extended grip', 'is_correct' => false]]],
                                ['question' => 'Where should the needle be grasped by the needle holder?', 'explanation' => 'The needle should be grasped at approximately one-third to one-half of the way from the swage (where suture attaches) for optimal control.', 'options' => [['label' => 'A', 'text' => 'At the tip', 'is_correct' => false], ['label' => 'B', 'text' => 'At the swage end', 'is_correct' => false], ['label' => 'C', 'text' => 'At one-third to one-half from the swage', 'is_correct' => true], ['label' => 'D', 'text' => 'Anywhere along the needle body', 'is_correct' => false]]],
                                ['question' => 'Which suture technique gives the best cosmetic result for skin closure?', 'explanation' => 'Subcuticular (intradermal) sutures run within the dermis and avoid external suture marks, providing the best cosmetic outcome.', 'options' => [['label' => 'A', 'text' => 'Simple interrupted', 'is_correct' => false], ['label' => 'B', 'text' => 'Horizontal mattress', 'is_correct' => false], ['label' => 'C', 'text' => 'Figure-of-eight', 'is_correct' => false], ['label' => 'D', 'text' => 'Subcuticular', 'is_correct' => true]]],
                                ['question' => 'What suture material is preferred for skin closure in cosmetic areas?', 'explanation' => 'Non-absorbable monofilament sutures (like nylon/Ethilon or polypropylene/Prolene) cause minimal tissue reaction and give better cosmetic results.', 'options' => [['label' => 'A', 'text' => 'Chromic catgut', 'is_correct' => false], ['label' => 'B', 'text' => 'Silk sutures', 'is_correct' => false], ['label' => 'C', 'text' => 'Nylon monofilament', 'is_correct' => true], ['label' => 'D', 'text' => 'Polyglycolic acid', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Knot Tying and Wound Management',
                        'lessons' => [
                            ['title' => 'Square Knot and Surgeon Knot', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Instrument Tie Technique', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Wound Assessment and Classification', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Wound Closure Decision Making', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Knot Tying and Wound Management Quiz',
                            'questions' => [
                                ['question' => 'How many throws are recommended for a secure square knot with monofilament suture?', 'explanation' => 'Monofilament sutures have greater memory and are more prone to slipping, so 4-5 throws are recommended for a secure knot.', 'options' => [['label' => 'A', 'text' => '2 throws', 'is_correct' => false], ['label' => 'B', 'text' => '3 throws', 'is_correct' => false], ['label' => 'C', 'text' => '4-5 throws', 'is_correct' => true], ['label' => 'D', 'text' => '8-10 throws', 'is_correct' => false]]],
                                ['question' => 'A contaminated wound older than 6 hours should typically be managed by?', 'explanation' => 'Contaminated wounds older than 6 hours (golden period) are typically managed by delayed primary closure or secondary intention to reduce infection risk.', 'options' => [['label' => 'A', 'text' => 'Immediate primary closure', 'is_correct' => false], ['label' => 'B', 'text' => 'Delayed primary closure or secondary intention', 'is_correct' => true], ['label' => 'C', 'text' => 'Skin grafting', 'is_correct' => false], ['label' => 'D', 'text' => 'Tissue flap coverage', 'is_correct' => false]]],
                                ['question' => 'What distinguishes a surgeon\'s knot from a square knot?', 'explanation' => 'A surgeon\'s knot has a double throw on the first tie, which provides more friction and helps maintain tension while the second throw is placed.', 'options' => [['label' => 'A', 'text' => 'It uses a different suture material', 'is_correct' => false], ['label' => 'B', 'text' => 'The first throw is a double throw for extra friction', 'is_correct' => true], ['label' => 'C', 'text' => 'It requires instrument assistance', 'is_correct' => false], ['label' => 'D', 'text' => 'It uses more throws total', 'is_correct' => false]]],
                                ['question' => 'Which wound classification has the highest infection rate?', 'explanation' => 'Dirty/infected wounds (Class IV) have the highest infection rate at >27%, as they involve existing infection, devitalized tissue, or fecal contamination.', 'options' => [['label' => 'A', 'text' => 'Clean (Class I)', 'is_correct' => false], ['label' => 'B', 'text' => 'Clean-contaminated (Class II)', 'is_correct' => false], ['label' => 'C', 'text' => 'Contaminated (Class III)', 'is_correct' => false], ['label' => 'D', 'text' => 'Dirty/infected (Class IV)', 'is_correct' => true]]],
                                ['question' => 'When performing an instrument tie, the suture is wrapped around which instrument?', 'explanation' => 'During an instrument tie, the suture is wrapped around the needle holder (or hemostat), then the free end is grasped and pulled through to form the knot.', 'options' => [['label' => 'A', 'text' => 'Tissue forceps', 'is_correct' => false], ['label' => 'B', 'text' => 'Needle holder', 'is_correct' => true], ['label' => 'C', 'text' => 'Retractor', 'is_correct' => false], ['label' => 'D', 'text' => 'Scissors', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 5
            [
                'title' => 'Dermatology Clinical Atlas',
                'instructor_email' => 'sara@spc-academy.com',
                'category_slug' => 'dermatology',
                'short_description' => 'Visual guide to clinical dermatology covering common and uncommon skin conditions with diagnostic approaches.',
                'description' => 'Dr. Sara El-Sayed presents a comprehensive visual atlas of dermatological conditions. Learn pattern recognition, diagnostic approaches, and treatment strategies through high-quality clinical images and case discussions.',
                'price' => 1200,
                'original_price' => 1560,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Basic dermatology knowledge', 'Understanding of skin anatomy', 'Clinical exposure recommended'],
                'learning_outcomes' => ['Identify common skin conditions', 'Apply systematic dermatological examination', 'Understand treatment modalities', 'Use dermatoscopy basics'],
                'tags' => ['dermatology', 'skin conditions', 'clinical atlas', 'diagnosis'],
                'modules' => [
                    [
                        'title' => 'Inflammatory Skin Conditions',
                        'lessons' => [
                            ['title' => 'Introduction to Dermatological Assessment', 'type' => 'video', 'duration_minutes' => 20, 'is_free' => true],
                            ['title' => 'Eczema and Dermatitis Types', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Psoriasis: Diagnosis and Variants', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Inflammatory Skin Conditions Quiz',
                            'questions' => [
                                ['question' => 'What is the Auspitz sign associated with?', 'explanation' => 'The Auspitz sign (pinpoint bleeding on removal of scales) is characteristic of psoriasis due to the thin suprapapillary epidermis.', 'options' => [['label' => 'A', 'text' => 'Eczema', 'is_correct' => false], ['label' => 'B', 'text' => 'Psoriasis', 'is_correct' => true], ['label' => 'C', 'text' => 'Lichen planus', 'is_correct' => false], ['label' => 'D', 'text' => 'Pityriasis rosea', 'is_correct' => false]]],
                                ['question' => 'Which type of eczema is most common in adults?', 'explanation' => 'Contact dermatitis (both allergic and irritant) is the most common form of eczema in adults, particularly related to occupational exposure.', 'options' => [['label' => 'A', 'text' => 'Atopic dermatitis', 'is_correct' => false], ['label' => 'B', 'text' => 'Contact dermatitis', 'is_correct' => true], ['label' => 'C', 'text' => 'Nummular eczema', 'is_correct' => false], ['label' => 'D', 'text' => 'Seborrheic dermatitis', 'is_correct' => false]]],
                                ['question' => 'The Koebner phenomenon is seen in which condition?', 'explanation' => 'Koebner phenomenon (isomorphic response) is the development of lesions at sites of trauma, classically seen in psoriasis, lichen planus, and vitiligo.', 'options' => [['label' => 'A', 'text' => 'Psoriasis', 'is_correct' => true], ['label' => 'B', 'text' => 'Acne vulgaris', 'is_correct' => false], ['label' => 'C', 'text' => 'Rosacea', 'is_correct' => false], ['label' => 'D', 'text' => 'Urticaria', 'is_correct' => false]]],
                                ['question' => 'What is the first-line treatment for mild-moderate psoriasis?', 'explanation' => 'Topical corticosteroids are the first-line treatment for mild to moderate plaque psoriasis, often combined with vitamin D analogs.', 'options' => [['label' => 'A', 'text' => 'Oral methotrexate', 'is_correct' => false], ['label' => 'B', 'text' => 'Biologic therapy', 'is_correct' => false], ['label' => 'C', 'text' => 'Topical corticosteroids', 'is_correct' => true], ['label' => 'D', 'text' => 'Phototherapy', 'is_correct' => false]]],
                                ['question' => 'Herald patch is the initial presentation of which condition?', 'explanation' => 'Pityriasis rosea classically begins with a herald patch (a single oval, scaly plaque) followed by a widespread eruption in a Christmas tree pattern.', 'options' => [['label' => 'A', 'text' => 'Tinea corporis', 'is_correct' => false], ['label' => 'B', 'text' => 'Pityriasis rosea', 'is_correct' => true], ['label' => 'C', 'text' => 'Secondary syphilis', 'is_correct' => false], ['label' => 'D', 'text' => 'Drug eruption', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Infectious Skin Conditions',
                        'lessons' => [
                            ['title' => 'Bacterial Skin Infections', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Fungal Infections of Skin and Nails', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Viral Skin Infections', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Infectious Skin Conditions Quiz',
                            'questions' => [
                                ['question' => 'Which organism most commonly causes impetigo?', 'explanation' => 'Staphylococcus aureus is the most common cause of impetigo (both bullous and non-bullous), followed by Streptococcus pyogenes.', 'options' => [['label' => 'A', 'text' => 'Streptococcus pyogenes only', 'is_correct' => false], ['label' => 'B', 'text' => 'Staphylococcus aureus', 'is_correct' => true], ['label' => 'C', 'text' => 'Pseudomonas aeruginosa', 'is_correct' => false], ['label' => 'D', 'text' => 'Candida albicans', 'is_correct' => false]]],
                                ['question' => 'KOH preparation is used to diagnose which type of skin infection?', 'explanation' => 'KOH (potassium hydroxide) preparation dissolves keratin and allows visualization of fungal hyphae and spores under microscopy.', 'options' => [['label' => 'A', 'text' => 'Bacterial infections', 'is_correct' => false], ['label' => 'B', 'text' => 'Viral infections', 'is_correct' => false], ['label' => 'C', 'text' => 'Fungal infections', 'is_correct' => true], ['label' => 'D', 'text' => 'Parasitic infections', 'is_correct' => false]]],
                                ['question' => 'Tzanck smear showing multinucleated giant cells suggests?', 'explanation' => 'Multinucleated giant cells on Tzanck smear are characteristic of herpes virus infections (HSV and VZV), though PCR is now the gold standard.', 'options' => [['label' => 'A', 'text' => 'Bacterial infection', 'is_correct' => false], ['label' => 'B', 'text' => 'Herpes virus infection', 'is_correct' => true], ['label' => 'C', 'text' => 'Fungal infection', 'is_correct' => false], ['label' => 'D', 'text' => 'Autoimmune blistering disease', 'is_correct' => false]]],
                                ['question' => 'What is the characteristic distribution of dermatophyte infection?', 'explanation' => 'Dermatophyte infections show annular (ring-shaped) lesions with central clearing and an active scaly, raised border.', 'options' => [['label' => 'A', 'text' => 'Linear distribution', 'is_correct' => false], ['label' => 'B', 'text' => 'Annular lesion with central clearing', 'is_correct' => true], ['label' => 'C', 'text' => 'Grouped vesicles on erythematous base', 'is_correct' => false], ['label' => 'D', 'text' => 'Diffuse erythema without scaling', 'is_correct' => false]]],
                                ['question' => 'Molluscum contagiosum is caused by which type of virus?', 'explanation' => 'Molluscum contagiosum is caused by a poxvirus (Molluscum contagiosum virus) and presents with dome-shaped, umbilicated papules.', 'options' => [['label' => 'A', 'text' => 'Herpes virus', 'is_correct' => false], ['label' => 'B', 'text' => 'Human papillomavirus', 'is_correct' => false], ['label' => 'C', 'text' => 'Poxvirus', 'is_correct' => true], ['label' => 'D', 'text' => 'Adenovirus', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pigmentary Disorders and Skin Tumors',
                        'lessons' => [
                            ['title' => 'Vitiligo and Melasma', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Benign Skin Tumors', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Melanoma and ABCDE Criteria', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Dermatoscopy Basics', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Pigmentary Disorders and Skin Tumors Quiz',
                            'questions' => [
                                ['question' => 'What does the "B" in ABCDE melanoma criteria stand for?', 'explanation' => 'ABCDE criteria: Asymmetry, Border irregularity, Color variation, Diameter >6mm, Evolving. B stands for Border irregularity.', 'options' => [['label' => 'A', 'text' => 'Bleeding', 'is_correct' => false], ['label' => 'B', 'text' => 'Border irregularity', 'is_correct' => true], ['label' => 'C', 'text' => 'Brownish color', 'is_correct' => false], ['label' => 'D', 'text' => 'Basal involvement', 'is_correct' => false]]],
                                ['question' => 'Which is the most common type of skin cancer?', 'explanation' => 'Basal cell carcinoma (BCC) is the most common skin cancer and the most common malignancy overall in humans.', 'options' => [['label' => 'A', 'text' => 'Melanoma', 'is_correct' => false], ['label' => 'B', 'text' => 'Squamous cell carcinoma', 'is_correct' => false], ['label' => 'C', 'text' => 'Basal cell carcinoma', 'is_correct' => true], ['label' => 'D', 'text' => 'Merkel cell carcinoma', 'is_correct' => false]]],
                                ['question' => 'Vitiligo is caused by destruction of which cells?', 'explanation' => 'Vitiligo results from autoimmune destruction of melanocytes, leading to depigmented macules and patches.', 'options' => [['label' => 'A', 'text' => 'Keratinocytes', 'is_correct' => false], ['label' => 'B', 'text' => 'Melanocytes', 'is_correct' => true], ['label' => 'C', 'text' => 'Langerhans cells', 'is_correct' => false], ['label' => 'D', 'text' => 'Merkel cells', 'is_correct' => false]]],
                                ['question' => 'What is the Breslow thickness?', 'explanation' => 'Breslow thickness measures the depth of melanoma invasion in millimeters from the granular layer to the deepest tumor cell. It is the most important prognostic factor.', 'options' => [['label' => 'A', 'text' => 'Horizontal diameter of the lesion', 'is_correct' => false], ['label' => 'B', 'text' => 'Depth of melanoma invasion in millimeters', 'is_correct' => true], ['label' => 'C', 'text' => 'Number of mitotic figures', 'is_correct' => false], ['label' => 'D', 'text' => 'Degree of lymphocytic infiltration', 'is_correct' => false]]],
                                ['question' => 'Melasma is most commonly triggered by which factor?', 'explanation' => 'Melasma is triggered by hormonal changes (pregnancy, oral contraceptives) and UV exposure, making it common in women of reproductive age.', 'options' => [['label' => 'A', 'text' => 'Bacterial infection', 'is_correct' => false], ['label' => 'B', 'text' => 'Autoimmune process', 'is_correct' => false], ['label' => 'C', 'text' => 'Hormonal changes and UV exposure', 'is_correct' => true], ['label' => 'D', 'text' => 'Fungal colonization', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 6
            [
                'title' => 'Obstetric Emergencies Management',
                'instructor_email' => 'mona@spc-academy.com',
                'category_slug' => 'obstetrics',
                'short_description' => 'Critical management of obstetric emergencies including postpartum hemorrhage, eclampsia, and shoulder dystocia.',
                'description' => 'Learn to manage life-threatening obstetric emergencies with confidence. Dr. Mona Ibrahim covers systematic approaches to common and uncommon obstetric emergencies with simulation-based case scenarios.',
                'price' => 1600,
                'original_price' => 2080,
                'level' => 'advanced',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Basic obstetrics knowledge', 'Clinical rotation experience', 'Understanding of maternal physiology'],
                'learning_outcomes' => ['Manage postpartum hemorrhage', 'Recognize and treat pre-eclampsia/eclampsia', 'Handle shoulder dystocia', 'Respond to obstetric emergencies systematically'],
                'tags' => ['obstetrics', 'emergency', 'maternal care', 'PPH', 'eclampsia'],
                'modules' => [
                    [
                        'title' => 'Postpartum Hemorrhage',
                        'lessons' => [
                            ['title' => 'Introduction to Obstetric Emergencies', 'type' => 'video', 'duration_minutes' => 20, 'is_free' => true],
                            ['title' => 'PPH: Causes and Risk Factors (4 Ts)', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Stepwise Management of PPH', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Uterotonic Agents Reference', 'type' => 'reading', 'content' => 'Reference guide for uterotonic medications including oxytocin, ergometrine, carboprost, and misoprostol dosing.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Postpartum Hemorrhage Quiz',
                            'questions' => [
                                ['question' => 'What are the 4 Ts of PPH causes?', 'explanation' => 'The 4 Ts are Tone (uterine atony - most common), Trauma (lacerations), Tissue (retained placenta), and Thrombin (coagulopathy).', 'options' => [['label' => 'A', 'text' => 'Tone, Trauma, Tissue, Thrombin', 'is_correct' => true], ['label' => 'B', 'text' => 'Temperature, Tachycardia, Tension, Thrombosis', 'is_correct' => false], ['label' => 'C', 'text' => 'Tear, Torsion, Tumor, Toxemia', 'is_correct' => false], ['label' => 'D', 'text' => 'Tone, Tear, Tumor, Transfusion', 'is_correct' => false]]],
                                ['question' => 'What is the most common cause of primary PPH?', 'explanation' => 'Uterine atony (failure of the uterus to contract after delivery) accounts for approximately 70-80% of all primary PPH cases.', 'options' => [['label' => 'A', 'text' => 'Cervical laceration', 'is_correct' => false], ['label' => 'B', 'text' => 'Retained placenta', 'is_correct' => false], ['label' => 'C', 'text' => 'Uterine atony', 'is_correct' => true], ['label' => 'D', 'text' => 'Coagulopathy', 'is_correct' => false]]],
                                ['question' => 'Primary PPH is defined as blood loss exceeding how much within 24 hours of delivery?', 'explanation' => 'Primary PPH is defined as blood loss >=500 mL after vaginal delivery or >=1000 mL after cesarean section within 24 hours of delivery.', 'options' => [['label' => 'A', 'text' => '250 mL', 'is_correct' => false], ['label' => 'B', 'text' => '500 mL after vaginal delivery', 'is_correct' => true], ['label' => 'C', 'text' => '1500 mL', 'is_correct' => false], ['label' => 'D', 'text' => '2000 mL', 'is_correct' => false]]],
                                ['question' => 'What is the first-line uterotonic for PPH management?', 'explanation' => 'Oxytocin is the first-line uterotonic agent for both prevention and treatment of PPH due to its rapid onset and safety profile.', 'options' => [['label' => 'A', 'text' => 'Ergometrine', 'is_correct' => false], ['label' => 'B', 'text' => 'Oxytocin', 'is_correct' => true], ['label' => 'C', 'text' => 'Carboprost', 'is_correct' => false], ['label' => 'D', 'text' => 'Misoprostol', 'is_correct' => false]]],
                                ['question' => 'In which scenario is ergometrine contraindicated?', 'explanation' => 'Ergometrine is contraindicated in hypertensive disorders (pre-eclampsia, eclampsia) as it can cause dangerous vasoconstriction and hypertension.', 'options' => [['label' => 'A', 'text' => 'Multiple pregnancy', 'is_correct' => false], ['label' => 'B', 'text' => 'Hypertensive disorders of pregnancy', 'is_correct' => true], ['label' => 'C', 'text' => 'Gestational diabetes', 'is_correct' => false], ['label' => 'D', 'text' => 'Preterm delivery', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Hypertensive Disorders of Pregnancy',
                        'lessons' => [
                            ['title' => 'Pre-eclampsia: Diagnosis and Classification', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Eclampsia Management Protocol', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'HELLP Syndrome', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Hypertensive Disorders Quiz',
                            'questions' => [
                                ['question' => 'What is the drug of choice for seizure prophylaxis in severe pre-eclampsia?', 'explanation' => 'Magnesium sulfate is the gold standard for seizure prophylaxis and treatment in pre-eclampsia/eclampsia, superior to diazepam or phenytoin.', 'options' => [['label' => 'A', 'text' => 'Diazepam', 'is_correct' => false], ['label' => 'B', 'text' => 'Phenytoin', 'is_correct' => false], ['label' => 'C', 'text' => 'Magnesium sulfate', 'is_correct' => true], ['label' => 'D', 'text' => 'Labetalol', 'is_correct' => false]]],
                                ['question' => 'What does HELLP syndrome stand for?', 'explanation' => 'HELLP stands for Hemolysis, Elevated Liver enzymes, and Low Platelets - a severe variant of pre-eclampsia.', 'options' => [['label' => 'A', 'text' => 'Hemolysis, Elevated Liver enzymes, Low Platelets', 'is_correct' => true], ['label' => 'B', 'text' => 'Hypertension, Edema, Liver damage, Low Protein', 'is_correct' => false], ['label' => 'C', 'text' => 'Hemorrhage, Elevated Lactate, Low Perfusion', 'is_correct' => false], ['label' => 'D', 'text' => 'Hepatitis, Elevated Lipids, Low Platelets', 'is_correct' => false]]],
                                ['question' => 'What is the definitive treatment for severe pre-eclampsia?', 'explanation' => 'Delivery of the baby and placenta is the only definitive cure for pre-eclampsia, as the condition resolves once the placenta is removed.', 'options' => [['label' => 'A', 'text' => 'Antihypertensive therapy', 'is_correct' => false], ['label' => 'B', 'text' => 'Bed rest', 'is_correct' => false], ['label' => 'C', 'text' => 'Delivery', 'is_correct' => true], ['label' => 'D', 'text' => 'Plasma exchange', 'is_correct' => false]]],
                                ['question' => 'At what BP level should antihypertensive treatment be initiated in pre-eclampsia?', 'explanation' => 'Antihypertensive treatment should be initiated when systolic BP >=160 mmHg or diastolic BP >=110 mmHg to prevent cerebrovascular complications.', 'options' => [['label' => 'A', 'text' => 'Systolic >=140 or diastolic >=90 mmHg', 'is_correct' => false], ['label' => 'B', 'text' => 'Systolic >=160 or diastolic >=110 mmHg', 'is_correct' => true], ['label' => 'C', 'text' => 'Systolic >=180 or diastolic >=120 mmHg', 'is_correct' => false], ['label' => 'D', 'text' => 'Any elevated BP reading', 'is_correct' => false]]],
                                ['question' => 'What is the antidote for magnesium sulfate toxicity?', 'explanation' => 'Calcium gluconate (10 mL of 10% solution IV over 10 minutes) is the specific antidote for magnesium sulfate toxicity.', 'options' => [['label' => 'A', 'text' => 'Sodium bicarbonate', 'is_correct' => false], ['label' => 'B', 'text' => 'Calcium gluconate', 'is_correct' => true], ['label' => 'C', 'text' => 'Naloxone', 'is_correct' => false], ['label' => 'D', 'text' => 'Atropine', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Shoulder Dystocia and Cord Emergencies',
                        'lessons' => [
                            ['title' => 'Shoulder Dystocia Recognition and Management', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'HELPERR Mnemonic Application', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Cord Prolapse Emergency Protocol', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Shoulder Dystocia and Cord Emergencies Quiz',
                            'questions' => [
                                ['question' => 'What is the first maneuver in the HELPERR protocol for shoulder dystocia?', 'explanation' => 'HELPERR: Help (call for help), Evaluate for episiotomy, Legs (McRoberts position), Pressure (suprapubic), Enter (rotational maneuvers), Remove posterior arm, Roll patient.', 'options' => [['label' => 'A', 'text' => 'McRoberts maneuver', 'is_correct' => false], ['label' => 'B', 'text' => 'Call for Help', 'is_correct' => true], ['label' => 'C', 'text' => 'Suprapubic pressure', 'is_correct' => false], ['label' => 'D', 'text' => 'Episiotomy', 'is_correct' => false]]],
                                ['question' => 'McRoberts maneuver involves positioning the patient how?', 'explanation' => 'McRoberts maneuver involves hyperflexion of the maternal thighs against the abdomen, which straightens the sacrum and increases the AP diameter of the pelvis.', 'options' => [['label' => 'A', 'text' => 'Hands and knees position', 'is_correct' => false], ['label' => 'B', 'text' => 'Hyperflexion of thighs against the abdomen', 'is_correct' => true], ['label' => 'C', 'text' => 'Left lateral position', 'is_correct' => false], ['label' => 'D', 'text' => 'Trendelenburg position', 'is_correct' => false]]],
                                ['question' => 'What is the initial management of cord prolapse?', 'explanation' => 'Immediate management includes elevating the presenting part off the cord (manual displacement), placing the patient in knee-chest or Trendelenburg position, and preparing for emergency cesarean section.', 'options' => [['label' => 'A', 'text' => 'Immediate vaginal delivery', 'is_correct' => false], ['label' => 'B', 'text' => 'Elevate presenting part and prepare for emergency cesarean', 'is_correct' => true], ['label' => 'C', 'text' => 'Apply fundal pressure', 'is_correct' => false], ['label' => 'D', 'text' => 'Administer tocolytics and wait', 'is_correct' => false]]],
                                ['question' => 'Suprapubic pressure in shoulder dystocia is applied in which direction?', 'explanation' => 'Suprapubic pressure should be applied posteriorly and laterally (toward the fetal face) to adduct the anterior shoulder and reduce the bisacromial diameter.', 'options' => [['label' => 'A', 'text' => 'Directly downward', 'is_correct' => false], ['label' => 'B', 'text' => 'Posteriorly and laterally toward the fetal face', 'is_correct' => true], ['label' => 'C', 'text' => 'Upward toward the fundus', 'is_correct' => false], ['label' => 'D', 'text' => 'Anterior and medially', 'is_correct' => false]]],
                                ['question' => 'Which is the most serious complication of shoulder dystocia for the neonate?', 'explanation' => 'Brachial plexus injury (Erb palsy) is the most common neonatal complication, but birth asphyxia is the most serious as it can lead to permanent neurological damage or death.', 'options' => [['label' => 'A', 'text' => 'Clavicle fracture', 'is_correct' => false], ['label' => 'B', 'text' => 'Birth asphyxia', 'is_correct' => true], ['label' => 'C', 'text' => 'Caput succedaneum', 'is_correct' => false], ['label' => 'D', 'text' => 'Cephalohematoma', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 7
            [
                'title' => 'Advanced Cardiac Life Support (ACLS)',
                'instructor_email' => 'khaled@spc-academy.com',
                'category_slug' => 'cardiology',
                'short_description' => 'Complete ACLS preparation covering cardiac arrest algorithms, post-arrest care, and acute coronary syndromes.',
                'description' => 'Prepare for your ACLS certification or refresh your knowledge with this comprehensive course by Dr. Khaled Mostafa. Covers all ACLS algorithms with practical case-based simulations and ECG rhythm recognition.',
                'price' => 2000,
                'original_price' => 2600,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['BLS certification', 'ECG basics', 'Clinical experience preferred'],
                'learning_outcomes' => ['Master ACLS algorithms', 'Manage cardiac arrest effectively', 'Recognize and treat arrhythmias', 'Apply post-cardiac arrest care'],
                'tags' => ['ACLS', 'cardiology', 'cardiac arrest', 'emergency', 'certification'],
                'modules' => [
                    [
                        'title' => 'ACLS Cardiac Arrest Algorithm',
                        'lessons' => [
                            ['title' => 'Introduction to ACLS', 'type' => 'video', 'duration_minutes' => 20, 'is_free' => true],
                            ['title' => 'VF/pVT Cardiac Arrest Algorithm', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Asystole/PEA Algorithm', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'ACLS Algorithms Quick Reference', 'type' => 'reading', 'content' => 'Printable reference cards for all ACLS cardiac arrest and bradycardia/tachycardia algorithms.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'ACLS Cardiac Arrest Quiz',
                            'questions' => [
                                ['question' => 'What is the recommended energy for the first defibrillation attempt in adult VF?', 'explanation' => 'For biphasic defibrillators, the initial dose is typically 120-200 J (manufacturer-specific). For monophasic, 360 J is used.', 'options' => [['label' => 'A', 'text' => '50 J', 'is_correct' => false], ['label' => 'B', 'text' => '120-200 J biphasic', 'is_correct' => true], ['label' => 'C', 'text' => '300 J monophasic', 'is_correct' => false], ['label' => 'D', 'text' => '400 J', 'is_correct' => false]]],
                                ['question' => 'How often should epinephrine be administered during cardiac arrest?', 'explanation' => 'Epinephrine 1 mg IV/IO should be given every 3-5 minutes during cardiac arrest for both shockable and non-shockable rhythms.', 'options' => [['label' => 'A', 'text' => 'Every 1-2 minutes', 'is_correct' => false], ['label' => 'B', 'text' => 'Every 3-5 minutes', 'is_correct' => true], ['label' => 'C', 'text' => 'Every 10 minutes', 'is_correct' => false], ['label' => 'D', 'text' => 'Only once', 'is_correct' => false]]],
                                ['question' => 'What drug may be given after the third shock in refractory VF?', 'explanation' => 'Amiodarone 300 mg IV bolus is recommended after the third shock in VF/pVT, followed by 150 mg if VF persists.', 'options' => [['label' => 'A', 'text' => 'Lidocaine 100 mg only', 'is_correct' => false], ['label' => 'B', 'text' => 'Amiodarone 300 mg', 'is_correct' => true], ['label' => 'C', 'text' => 'Atropine 1 mg', 'is_correct' => false], ['label' => 'D', 'text' => 'Adenosine 6 mg', 'is_correct' => false]]],
                                ['question' => 'What is the recommended compression rate in adult CPR?', 'explanation' => 'The AHA recommends chest compressions at a rate of 100-120 per minute with a depth of at least 2 inches (5 cm) for adults.', 'options' => [['label' => 'A', 'text' => '60-80 per minute', 'is_correct' => false], ['label' => 'B', 'text' => '80-100 per minute', 'is_correct' => false], ['label' => 'C', 'text' => '100-120 per minute', 'is_correct' => true], ['label' => 'D', 'text' => '120-150 per minute', 'is_correct' => false]]],
                                ['question' => 'Which H is NOT part of the reversible causes (Hs and Ts)?', 'explanation' => 'The Hs are Hypovolemia, Hypoxia, Hydrogen ion (acidosis), Hypo/Hyperkalemia, and Hypothermia. Hypertension is not one of the Hs.', 'options' => [['label' => 'A', 'text' => 'Hypovolemia', 'is_correct' => false], ['label' => 'B', 'text' => 'Hypothermia', 'is_correct' => false], ['label' => 'C', 'text' => 'Hypertension', 'is_correct' => true], ['label' => 'D', 'text' => 'Hypoxia', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Tachycardia and Bradycardia Algorithms',
                        'lessons' => [
                            ['title' => 'Adult Tachycardia with Pulse Algorithm', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Adult Bradycardia Algorithm', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Rhythm Identification Practice', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Tachycardia and Bradycardia Quiz',
                            'questions' => [
                                ['question' => 'What is the first-line treatment for unstable tachycardia with a pulse?', 'explanation' => 'Synchronized cardioversion is the immediate treatment for any unstable tachycardia with a pulse (signs: hypotension, altered consciousness, chest pain, acute HF).', 'options' => [['label' => 'A', 'text' => 'Adenosine', 'is_correct' => false], ['label' => 'B', 'text' => 'Synchronized cardioversion', 'is_correct' => true], ['label' => 'C', 'text' => 'Amiodarone', 'is_correct' => false], ['label' => 'D', 'text' => 'Vagal maneuvers', 'is_correct' => false]]],
                                ['question' => 'What dose of atropine is used for symptomatic bradycardia in adults?', 'explanation' => 'Atropine 1 mg IV is given every 3-5 minutes, up to a maximum total dose of 3 mg for symptomatic bradycardia.', 'options' => [['label' => 'A', 'text' => '0.5 mg IV, max 3 mg', 'is_correct' => true], ['label' => 'B', 'text' => '1 mg IV once only', 'is_correct' => false], ['label' => 'C', 'text' => '2 mg IV, max 6 mg', 'is_correct' => false], ['label' => 'D', 'text' => '0.1 mg IV', 'is_correct' => false]]],
                                ['question' => 'Transcutaneous pacing is indicated when?', 'explanation' => 'Transcutaneous pacing is indicated for symptomatic bradycardia unresponsive to atropine, especially in Mobitz Type II or third-degree heart block.', 'options' => [['label' => 'A', 'text' => 'As first-line for all bradycardias', 'is_correct' => false], ['label' => 'B', 'text' => 'When atropine fails in symptomatic bradycardia', 'is_correct' => true], ['label' => 'C', 'text' => 'Only for asystole', 'is_correct' => false], ['label' => 'D', 'text' => 'For sinus bradycardia in athletes', 'is_correct' => false]]],
                                ['question' => 'The initial dose of adenosine for regular narrow-complex SVT is?', 'explanation' => 'Adenosine 6 mg rapid IV push is the initial dose for regular narrow-complex SVT, followed by 12 mg if the first dose is ineffective.', 'options' => [['label' => 'A', 'text' => '3 mg rapid IV push', 'is_correct' => false], ['label' => 'B', 'text' => '6 mg rapid IV push', 'is_correct' => true], ['label' => 'C', 'text' => '12 mg slow IV infusion', 'is_correct' => false], ['label' => 'D', 'text' => '18 mg IV push', 'is_correct' => false]]],
                                ['question' => 'What defines hemodynamic instability in a tachyarrhythmia patient?', 'explanation' => 'Signs of hemodynamic instability include hypotension, altered mental status, signs of shock, ischemic chest pain, and acute heart failure.', 'options' => [['label' => 'A', 'text' => 'Heart rate >100 bpm alone', 'is_correct' => false], ['label' => 'B', 'text' => 'Hypotension, altered consciousness, chest pain, or acute heart failure', 'is_correct' => true], ['label' => 'C', 'text' => 'QRS >0.12 seconds', 'is_correct' => false], ['label' => 'D', 'text' => 'Any irregular rhythm', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Post-Cardiac Arrest Care',
                        'lessons' => [
                            ['title' => 'Return of Spontaneous Circulation (ROSC)', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Targeted Temperature Management', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Acute Coronary Syndrome Management', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Post-Cardiac Arrest Care Quiz',
                            'questions' => [
                                ['question' => 'What is the target temperature for targeted temperature management (TTM)?', 'explanation' => 'Current guidelines recommend maintaining temperature between 32-36 degrees Celsius for at least 24 hours after ROSC in comatose patients.', 'options' => [['label' => 'A', 'text' => '30-32 degrees Celsius', 'is_correct' => false], ['label' => 'B', 'text' => '32-36 degrees Celsius', 'is_correct' => true], ['label' => 'C', 'text' => '36-37 degrees Celsius', 'is_correct' => false], ['label' => 'D', 'text' => '37-38 degrees Celsius', 'is_correct' => false]]],
                                ['question' => 'What is the oxygen saturation target after ROSC?', 'explanation' => 'After ROSC, oxygen should be titrated to maintain SpO2 94-98%. Hyperoxia should be avoided as it may worsen neurological outcomes.', 'options' => [['label' => 'A', 'text' => '100% at all times', 'is_correct' => false], ['label' => 'B', 'text' => '94-98%', 'is_correct' => true], ['label' => 'C', 'text' => '88-92%', 'is_correct' => false], ['label' => 'D', 'text' => '>99%', 'is_correct' => false]]],
                                ['question' => 'When should PCI be considered after cardiac arrest with STEMI?', 'explanation' => 'Emergency PCI is recommended for STEMI patients after cardiac arrest with ROSC, ideally within 90 minutes, regardless of coma status.', 'options' => [['label' => 'A', 'text' => 'Only if patient is conscious', 'is_correct' => false], ['label' => 'B', 'text' => 'Emergently, regardless of neurological status', 'is_correct' => true], ['label' => 'C', 'text' => 'Only after 72 hours of observation', 'is_correct' => false], ['label' => 'D', 'text' => 'PCI is contraindicated after cardiac arrest', 'is_correct' => false]]],
                                ['question' => 'Which is a key component of post-ROSC bundle of care?', 'explanation' => 'Post-ROSC care includes 12-lead ECG, hemodynamic optimization, targeted temperature management, mechanical ventilation optimization, and seizure management.', 'options' => [['label' => 'A', 'text' => 'Immediate discharge if alert', 'is_correct' => false], ['label' => 'B', 'text' => '12-lead ECG, hemodynamic optimization, and TTM', 'is_correct' => true], ['label' => 'C', 'text' => 'Routine prophylactic antibiotics', 'is_correct' => false], ['label' => 'D', 'text' => 'Aggressive fluid bolus regardless of BP', 'is_correct' => false]]],
                                ['question' => 'What is the target mean arterial pressure (MAP) after ROSC?', 'explanation' => 'A MAP >=65 mmHg is the minimum target to maintain adequate organ perfusion after ROSC, using IV fluids and vasopressors as needed.', 'options' => [['label' => 'A', 'text' => '>=50 mmHg', 'is_correct' => false], ['label' => 'B', 'text' => '>=65 mmHg', 'is_correct' => true], ['label' => 'C', 'text' => '>=80 mmHg', 'is_correct' => false], ['label' => 'D', 'text' => '>=100 mmHg', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 8
            [
                'title' => 'Pediatric Emergency Medicine',
                'instructor_email' => 'ahmed@spc-academy.com',
                'category_slug' => 'pediatrics',
                'short_description' => 'Essential pediatric emergency management covering common presentations from fever to seizures and respiratory distress.',
                'description' => 'Learn to manage pediatric emergencies confidently with Dr. Ahmed Hassan. This course covers the most common and critical pediatric presentations in the emergency department with systematic approaches.',
                'price' => 1400,
                'original_price' => 1820,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => true,
                'requirements' => ['Basic pediatric knowledge', 'Understanding of vital sign ranges by age', 'Clinical experience helpful'],
                'learning_outcomes' => ['Manage pediatric emergencies systematically', 'Recognize critically ill children', 'Apply age-appropriate treatment protocols', 'Communicate effectively with families'],
                'tags' => ['pediatrics', 'emergency medicine', 'pediatric ER', 'acute care'],
                'modules' => [
                    [
                        'title' => 'Pediatric Respiratory Emergencies',
                        'lessons' => [
                            ['title' => 'Approach to Pediatric Respiratory Distress', 'type' => 'video', 'duration_minutes' => 25, 'is_free' => true],
                            ['title' => 'Croup vs Epiglottitis', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Acute Asthma Exacerbation in Children', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Bronchiolitis Management', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Pediatric Respiratory Emergencies Quiz',
                            'questions' => [
                                ['question' => 'Which finding is most characteristic of croup?', 'explanation' => 'Croup (laryngotracheobronchitis) classically presents with a barking/seal-like cough, stridor, and hoarseness, typically worse at night.', 'options' => [['label' => 'A', 'text' => 'Expiratory wheeze', 'is_correct' => false], ['label' => 'B', 'text' => 'Barking cough with inspiratory stridor', 'is_correct' => true], ['label' => 'C', 'text' => 'Productive cough with fever', 'is_correct' => false], ['label' => 'D', 'text' => 'Silent chest', 'is_correct' => false]]],
                                ['question' => 'What is the classic X-ray finding in croup?', 'explanation' => 'The steeple sign (subglottic narrowing on AP neck X-ray) is the classic radiographic finding in croup.', 'options' => [['label' => 'A', 'text' => 'Thumb sign', 'is_correct' => false], ['label' => 'B', 'text' => 'Steeple sign', 'is_correct' => true], ['label' => 'C', 'text' => 'Sail sign', 'is_correct' => false], ['label' => 'D', 'text' => 'Butterfly pattern', 'is_correct' => false]]],
                                ['question' => 'What is the first-line treatment for moderate croup?', 'explanation' => 'Dexamethasone (single oral/IM dose of 0.15-0.6 mg/kg) is the first-line treatment for croup, with nebulized epinephrine for severe cases.', 'options' => [['label' => 'A', 'text' => 'Antibiotics', 'is_correct' => false], ['label' => 'B', 'text' => 'Dexamethasone', 'is_correct' => true], ['label' => 'C', 'text' => 'Inhaled salbutamol', 'is_correct' => false], ['label' => 'D', 'text' => 'Intubation', 'is_correct' => false]]],
                                ['question' => 'Which virus most commonly causes bronchiolitis?', 'explanation' => 'Respiratory Syncytial Virus (RSV) is responsible for approximately 50-80% of bronchiolitis cases in infants.', 'options' => [['label' => 'A', 'text' => 'Influenza A', 'is_correct' => false], ['label' => 'B', 'text' => 'Parainfluenza virus', 'is_correct' => false], ['label' => 'C', 'text' => 'Respiratory syncytial virus (RSV)', 'is_correct' => true], ['label' => 'D', 'text' => 'Adenovirus', 'is_correct' => false]]],
                                ['question' => 'What is the main management of bronchiolitis?', 'explanation' => 'Bronchiolitis management is primarily supportive: nasal suctioning, oxygen therapy, hydration. Bronchodilators and steroids are generally NOT recommended.', 'options' => [['label' => 'A', 'text' => 'Routine antibiotics and bronchodilators', 'is_correct' => false], ['label' => 'B', 'text' => 'Supportive care: suctioning, oxygen, hydration', 'is_correct' => true], ['label' => 'C', 'text' => 'Systemic corticosteroids', 'is_correct' => false], ['label' => 'D', 'text' => 'Antiviral therapy for all cases', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pediatric Seizures and Neurological Emergencies',
                        'lessons' => [
                            ['title' => 'Febrile Seizures Management', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Status Epilepticus Protocol', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Pediatric Meningitis Assessment', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Pediatric Neurological Emergencies Quiz',
                            'questions' => [
                                ['question' => 'What defines a simple febrile seizure?', 'explanation' => 'A simple febrile seizure is generalized, lasts <15 minutes, occurs once in 24 hours, and occurs in a child 6 months to 5 years with fever and no CNS infection.', 'options' => [['label' => 'A', 'text' => 'Generalized seizure, <15 min, once in 24 hours, age 6 months to 5 years', 'is_correct' => true], ['label' => 'B', 'text' => 'Any seizure with fever regardless of duration', 'is_correct' => false], ['label' => 'C', 'text' => 'Focal seizure lasting >30 minutes with fever', 'is_correct' => false], ['label' => 'D', 'text' => 'Seizure in a child with known epilepsy and fever', 'is_correct' => false]]],
                                ['question' => 'What is the first-line benzodiazepine for status epilepticus?', 'explanation' => 'IV lorazepam or IV/IM midazolam are first-line benzodiazepines for status epilepticus. Rectal diazepam is an alternative when IV access is not available.', 'options' => [['label' => 'A', 'text' => 'Phenobarbital', 'is_correct' => false], ['label' => 'B', 'text' => 'Lorazepam or midazolam', 'is_correct' => true], ['label' => 'C', 'text' => 'Phenytoin', 'is_correct' => false], ['label' => 'D', 'text' => 'Valproic acid', 'is_correct' => false]]],
                                ['question' => 'Status epilepticus is defined as seizure lasting longer than?', 'explanation' => 'Status epilepticus is defined as continuous seizure activity lasting >5 minutes, or >=2 seizures without return to baseline consciousness between them.', 'options' => [['label' => 'A', 'text' => '1 minute', 'is_correct' => false], ['label' => 'B', 'text' => '5 minutes', 'is_correct' => true], ['label' => 'C', 'text' => '15 minutes', 'is_correct' => false], ['label' => 'D', 'text' => '30 minutes', 'is_correct' => false]]],
                                ['question' => 'Which sign is NOT reliable for meningitis in infants under 18 months?', 'explanation' => 'Neck stiffness (nuchal rigidity) and Kernig/Brudzinski signs are unreliable in infants <18 months. A bulging fontanelle, irritability, and poor feeding are more important.', 'options' => [['label' => 'A', 'text' => 'Bulging fontanelle', 'is_correct' => false], ['label' => 'B', 'text' => 'Nuchal rigidity', 'is_correct' => true], ['label' => 'C', 'text' => 'Irritability', 'is_correct' => false], ['label' => 'D', 'text' => 'Poor feeding', 'is_correct' => false]]],
                                ['question' => 'What empirical antibiotic should be given for suspected bacterial meningitis in neonates?', 'explanation' => 'Neonatal meningitis is empirically treated with ampicillin (for Listeria and Group B Strep coverage) plus a third-generation cephalosporin (cefotaxime) or gentamicin.', 'options' => [['label' => 'A', 'text' => 'Ceftriaxone alone', 'is_correct' => false], ['label' => 'B', 'text' => 'Ampicillin plus cefotaxime or gentamicin', 'is_correct' => true], ['label' => 'C', 'text' => 'Vancomycin alone', 'is_correct' => false], ['label' => 'D', 'text' => 'Azithromycin plus metronidazole', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pediatric Shock and Fluid Management',
                        'lessons' => [
                            ['title' => 'Types of Pediatric Shock', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Fluid Resuscitation in Children', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Inotropes and Vasopressors in Pediatrics', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Pediatric Shock Quiz',
                            'questions' => [
                                ['question' => 'What is the initial fluid bolus for pediatric septic shock?', 'explanation' => 'The initial fluid bolus for pediatric septic shock is 20 mL/kg of isotonic crystalloid (normal saline or Ringer lactate) over 5-10 minutes, up to 60 mL/kg in the first hour.', 'options' => [['label' => 'A', 'text' => '10 mL/kg over 30 minutes', 'is_correct' => false], ['label' => 'B', 'text' => '20 mL/kg over 5-10 minutes', 'is_correct' => true], ['label' => 'C', 'text' => '40 mL/kg over 1 hour', 'is_correct' => false], ['label' => 'D', 'text' => '5 mL/kg over 15 minutes', 'is_correct' => false]]],
                                ['question' => 'Compensated shock in children is characterized by?', 'explanation' => 'In compensated shock, the body maintains blood pressure through compensatory mechanisms (tachycardia, vasoconstriction) but shows signs of poor perfusion (prolonged cap refill, cool extremities).', 'options' => [['label' => 'A', 'text' => 'Hypotension and bradycardia', 'is_correct' => false], ['label' => 'B', 'text' => 'Normal BP but signs of poor perfusion', 'is_correct' => true], ['label' => 'C', 'text' => 'Hypertension and tachycardia', 'is_correct' => false], ['label' => 'D', 'text' => 'Normal BP and normal perfusion', 'is_correct' => false]]],
                                ['question' => 'Which is the first-line vasopressor for fluid-refractory septic shock in children?', 'explanation' => 'Epinephrine is the first-line vasopressor for cold shock (poor perfusion, vasoconstricted), while norepinephrine is preferred for warm shock.', 'options' => [['label' => 'A', 'text' => 'Dopamine', 'is_correct' => false], ['label' => 'B', 'text' => 'Epinephrine for cold shock, norepinephrine for warm shock', 'is_correct' => true], ['label' => 'C', 'text' => 'Vasopressin', 'is_correct' => false], ['label' => 'D', 'text' => 'Phenylephrine', 'is_correct' => false]]],
                                ['question' => 'Hypotension in a child indicates which stage of shock?', 'explanation' => 'Hypotension in a child is a late and ominous sign indicating decompensated shock. Children maintain blood pressure until 25-30% of blood volume is lost.', 'options' => [['label' => 'A', 'text' => 'Early compensated shock', 'is_correct' => false], ['label' => 'B', 'text' => 'Decompensated (late) shock', 'is_correct' => true], ['label' => 'C', 'text' => 'Pre-shock', 'is_correct' => false], ['label' => 'D', 'text' => 'Resolved shock', 'is_correct' => false]]],
                                ['question' => 'What maintenance IV fluid is appropriate for a child with normal electrolytes?', 'explanation' => 'Isotonic fluids (normal saline or Ringer lactate) with appropriate dextrose are recommended for maintenance IV fluids in children to prevent hyponatremia.', 'options' => [['label' => 'A', 'text' => 'D5W with 0.2% NaCl', 'is_correct' => false], ['label' => 'B', 'text' => 'Isotonic saline with appropriate dextrose', 'is_correct' => true], ['label' => 'C', 'text' => 'Half-normal saline only', 'is_correct' => false], ['label' => 'D', 'text' => 'D10W without electrolytes', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 9
            [
                'title' => 'General Surgery Case Studies',
                'instructor_email' => 'omar@spc-academy.com',
                'category_slug' => 'surgery',
                'short_description' => 'In-depth surgical case studies covering acute abdomen, trauma, and common surgical conditions.',
                'description' => 'Dr. Omar Farouk presents a collection of challenging surgical case studies designed for surgical trainees. Learn clinical reasoning, operative decision-making, and post-operative management through real-world scenarios.',
                'price' => 2200,
                'original_price' => 2860,
                'level' => 'advanced',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Surgical anatomy knowledge', 'Basic surgical principles', 'Clinical rotation experience'],
                'learning_outcomes' => ['Analyze surgical cases systematically', 'Make operative decisions confidently', 'Manage post-operative complications', 'Apply evidence-based surgical practice'],
                'tags' => ['surgery', 'case studies', 'acute abdomen', 'trauma'],
                'modules' => [
                    [
                        'title' => 'Acute Abdomen Cases',
                        'lessons' => [
                            ['title' => 'Approach to Acute Abdomen', 'type' => 'video', 'duration_minutes' => 30, 'is_free' => true],
                            ['title' => 'Appendicitis: Typical and Atypical Presentations', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Small Bowel Obstruction Management', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Acute Abdomen Reference Guide', 'type' => 'reading', 'content' => 'Differential diagnosis guide for acute abdomen organized by location and clinical features.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Acute Abdomen Quiz',
                            'questions' => [
                                ['question' => 'What is the most common cause of small bowel obstruction?', 'explanation' => 'Adhesions from previous surgery are the most common cause of small bowel obstruction in developed countries, accounting for approximately 60-75% of cases.', 'options' => [['label' => 'A', 'text' => 'Hernia', 'is_correct' => false], ['label' => 'B', 'text' => 'Adhesions', 'is_correct' => true], ['label' => 'C', 'text' => 'Tumor', 'is_correct' => false], ['label' => 'D', 'text' => 'Volvulus', 'is_correct' => false]]],
                                ['question' => 'What is the Alvarado score used for?', 'explanation' => 'The Alvarado score (MANTRELS) is a clinical scoring system used to aid in the diagnosis of acute appendicitis, with a score >=7 suggesting high probability.', 'options' => [['label' => 'A', 'text' => 'Diagnosing cholecystitis', 'is_correct' => false], ['label' => 'B', 'text' => 'Diagnosing acute appendicitis', 'is_correct' => true], ['label' => 'C', 'text' => 'Grading bowel obstruction severity', 'is_correct' => false], ['label' => 'D', 'text' => 'Predicting operative risk', 'is_correct' => false]]],
                                ['question' => 'What X-ray finding is classic for small bowel obstruction?', 'explanation' => 'Multiple air-fluid levels with dilated small bowel loops (>3 cm) and absence of distal gas are classic findings of SBO on abdominal X-ray.', 'options' => [['label' => 'A', 'text' => 'Free air under diaphragm', 'is_correct' => false], ['label' => 'B', 'text' => 'Multiple air-fluid levels with dilated loops', 'is_correct' => true], ['label' => 'C', 'text' => 'Coffee bean sign', 'is_correct' => false], ['label' => 'D', 'text' => 'Thumb printing sign', 'is_correct' => false]]],
                                ['question' => 'McBurney point is located at?', 'explanation' => 'McBurney point is located at one-third of the distance from the right anterior superior iliac spine (ASIS) to the umbilicus, overlying the base of the appendix.', 'options' => [['label' => 'A', 'text' => 'Right upper quadrant', 'is_correct' => false], ['label' => 'B', 'text' => 'One-third distance from ASIS to umbilicus', 'is_correct' => true], ['label' => 'C', 'text' => 'Left lower quadrant', 'is_correct' => false], ['label' => 'D', 'text' => 'Periumbilical region', 'is_correct' => false]]],
                                ['question' => 'What sign indicates peritonitis on examination?', 'explanation' => 'Rebound tenderness (Blumberg sign) is the classic sign of peritoneal irritation/peritonitis, along with guarding and rigidity of the abdominal wall.', 'options' => [['label' => 'A', 'text' => 'Murphy sign', 'is_correct' => false], ['label' => 'B', 'text' => 'Rebound tenderness', 'is_correct' => true], ['label' => 'C', 'text' => 'Rovsing sign', 'is_correct' => false], ['label' => 'D', 'text' => 'Cullen sign', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Hepatobiliary Surgery Cases',
                        'lessons' => [
                            ['title' => 'Acute Cholecystitis Management', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Choledocholithiasis and Cholangitis', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Acute Pancreatitis: Surgical Perspective', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Hepatobiliary Surgery Quiz',
                            'questions' => [
                                ['question' => 'What is the Charcot triad of cholangitis?', 'explanation' => 'Charcot triad consists of fever, jaundice, and right upper quadrant pain. Reynolds pentad adds hypotension and altered mental status for severe cholangitis.', 'options' => [['label' => 'A', 'text' => 'Fever, jaundice, and right upper quadrant pain', 'is_correct' => true], ['label' => 'B', 'text' => 'Nausea, vomiting, and diarrhea', 'is_correct' => false], ['label' => 'C', 'text' => 'Fever, tachycardia, and hypotension', 'is_correct' => false], ['label' => 'D', 'text' => 'Jaundice, weight loss, and palpable gallbladder', 'is_correct' => false]]],
                                ['question' => 'What is the timing for laparoscopic cholecystectomy in acute cholecystitis?', 'explanation' => 'Early laparoscopic cholecystectomy (within 72 hours, ideally within 24 hours) is recommended for acute cholecystitis as it reduces total hospital stay and complications.', 'options' => [['label' => 'A', 'text' => 'After 6 weeks of cooling off', 'is_correct' => false], ['label' => 'B', 'text' => 'Within 72 hours (early cholecystectomy)', 'is_correct' => true], ['label' => 'C', 'text' => 'After complete resolution of symptoms', 'is_correct' => false], ['label' => 'D', 'text' => 'Only if conservative management fails', 'is_correct' => false]]],
                                ['question' => 'Which scoring system is used to predict severity of acute pancreatitis?', 'explanation' => 'The Ranson criteria (at admission and 48 hours) and APACHE II score are commonly used. The revised Atlanta classification is used for severity grading.', 'options' => [['label' => 'A', 'text' => 'Alvarado score', 'is_correct' => false], ['label' => 'B', 'text' => 'Ranson criteria', 'is_correct' => true], ['label' => 'C', 'text' => 'Child-Pugh score', 'is_correct' => false], ['label' => 'D', 'text' => 'Glasgow-Blatchford score', 'is_correct' => false]]],
                                ['question' => 'What is the most common cause of acute pancreatitis?', 'explanation' => 'Gallstones and alcohol are the two most common causes of acute pancreatitis, with gallstones being the single most common cause in most populations.', 'options' => [['label' => 'A', 'text' => 'Alcohol', 'is_correct' => false], ['label' => 'B', 'text' => 'Gallstones', 'is_correct' => true], ['label' => 'C', 'text' => 'Hypertriglyceridemia', 'is_correct' => false], ['label' => 'D', 'text' => 'ERCP', 'is_correct' => false]]],
                                ['question' => 'Murphy sign is positive in which condition?', 'explanation' => 'Murphy sign (inspiratory arrest during deep palpation of the right upper quadrant) is the classic physical examination finding for acute cholecystitis.', 'options' => [['label' => 'A', 'text' => 'Acute appendicitis', 'is_correct' => false], ['label' => 'B', 'text' => 'Acute cholecystitis', 'is_correct' => true], ['label' => 'C', 'text' => 'Acute pancreatitis', 'is_correct' => false], ['label' => 'D', 'text' => 'Peptic ulcer perforation', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Trauma Surgery Basics',
                        'lessons' => [
                            ['title' => 'ATLS Primary Survey', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Abdominal Trauma Assessment', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Chest Trauma Management', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Trauma Surgery Quiz',
                            'questions' => [
                                ['question' => 'What is the correct sequence of the ATLS primary survey?', 'explanation' => 'The ATLS primary survey follows ABCDE: Airway (with cervical spine protection), Breathing, Circulation (hemorrhage control), Disability (neurological), Exposure (environmental control).', 'options' => [['label' => 'A', 'text' => 'Airway, Breathing, Circulation, Disability, Exposure', 'is_correct' => true], ['label' => 'B', 'text' => 'Breathing, Airway, Circulation, Disability, Exposure', 'is_correct' => false], ['label' => 'C', 'text' => 'Circulation, Airway, Breathing, Disability, Exposure', 'is_correct' => false], ['label' => 'D', 'text' => 'Disability, Airway, Breathing, Circulation, Exposure', 'is_correct' => false]]],
                                ['question' => 'FAST exam in trauma evaluates for fluid in which areas?', 'explanation' => 'FAST (Focused Assessment with Sonography for Trauma) evaluates 4 areas: right upper quadrant (Morison pouch), left upper quadrant (splenorenal recess), pelvis (suprapubic), and subxiphoid (pericardium).', 'options' => [['label' => 'A', 'text' => 'Hepatorenal, splenorenal, pelvis, and pericardium', 'is_correct' => true], ['label' => 'B', 'text' => 'Chest, abdomen, and pelvis only', 'is_correct' => false], ['label' => 'C', 'text' => 'All four abdominal quadrants', 'is_correct' => false], ['label' => 'D', 'text' => 'Lungs, heart, and abdominal aorta', 'is_correct' => false]]],
                                ['question' => 'What is the most commonly injured solid organ in blunt abdominal trauma?', 'explanation' => 'The spleen is the most commonly injured solid organ in blunt abdominal trauma, followed by the liver.', 'options' => [['label' => 'A', 'text' => 'Liver', 'is_correct' => false], ['label' => 'B', 'text' => 'Spleen', 'is_correct' => true], ['label' => 'C', 'text' => 'Kidney', 'is_correct' => false], ['label' => 'D', 'text' => 'Pancreas', 'is_correct' => false]]],
                                ['question' => 'Tension pneumothorax requires immediate treatment with?', 'explanation' => 'Tension pneumothorax is a clinical diagnosis requiring immediate needle decompression (2nd intercostal space, midclavicular line) followed by chest tube insertion.', 'options' => [['label' => 'A', 'text' => 'Chest X-ray confirmation first', 'is_correct' => false], ['label' => 'B', 'text' => 'Needle decompression followed by chest tube', 'is_correct' => true], ['label' => 'C', 'text' => 'CT scan before intervention', 'is_correct' => false], ['label' => 'D', 'text' => 'Observation and oxygen therapy', 'is_correct' => false]]],
                                ['question' => 'Class III hemorrhagic shock involves blood loss of approximately?', 'explanation' => 'Class III hemorrhagic shock involves 30-40% blood volume loss (1500-2000 mL in adults), presenting with tachycardia, hypotension, tachypnea, and altered mental status.', 'options' => [['label' => 'A', 'text' => '<15%', 'is_correct' => false], ['label' => 'B', 'text' => '15-30%', 'is_correct' => false], ['label' => 'C', 'text' => '30-40%', 'is_correct' => true], ['label' => 'D', 'text' => '>40%', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 10
            [
                'title' => 'Internal Medicine Board Review',
                'instructor_email' => 'sara@spc-academy.com',
                'category_slug' => 'internal-medicine',
                'short_description' => 'Comprehensive board review covering all major internal medicine subspecialties with high-yield questions.',
                'description' => 'Prepare for your internal medicine board examinations with this structured review course. Dr. Sara El-Sayed covers high-yield topics across all subspecialties with practice questions and detailed explanations.',
                'price' => 3000,
                'original_price' => 3900,
                'level' => 'advanced',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Completed internal medicine residency or final year', 'Broad clinical knowledge base', 'Previous exam preparation helpful'],
                'learning_outcomes' => ['Review high-yield board topics', 'Practice exam-style questions', 'Identify knowledge gaps', 'Build exam-taking strategies'],
                'tags' => ['board review', 'internal medicine', 'exam prep', 'high yield'],
                'modules' => [
                    [
                        'title' => 'Hematology and Oncology Review',
                        'lessons' => [
                            ['title' => 'Anemia: Classification and Approach', 'type' => 'video', 'duration_minutes' => 35, 'is_free' => true],
                            ['title' => 'Coagulation Disorders Review', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Lymphoma and Leukemia Essentials', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Hematology and Oncology Quiz',
                            'questions' => [
                                ['question' => 'What is the most common cause of iron deficiency anemia in premenopausal women?', 'explanation' => 'Menstrual blood loss is the most common cause of iron deficiency anemia in premenopausal women.', 'options' => [['label' => 'A', 'text' => 'Poor dietary intake', 'is_correct' => false], ['label' => 'B', 'text' => 'Menstrual blood loss', 'is_correct' => true], ['label' => 'C', 'text' => 'GI bleeding', 'is_correct' => false], ['label' => 'D', 'text' => 'Malabsorption', 'is_correct' => false]]],
                                ['question' => 'Target cells on peripheral smear are seen in all EXCEPT?', 'explanation' => 'Target cells are seen in thalassemia, liver disease, hemoglobin C disease, and iron deficiency. They are NOT characteristic of G6PD deficiency (which shows bite cells).', 'options' => [['label' => 'A', 'text' => 'Thalassemia', 'is_correct' => false], ['label' => 'B', 'text' => 'Liver disease', 'is_correct' => false], ['label' => 'C', 'text' => 'G6PD deficiency', 'is_correct' => true], ['label' => 'D', 'text' => 'Iron deficiency anemia', 'is_correct' => false]]],
                                ['question' => 'Which factor deficiency causes the longest PT prolongation?', 'explanation' => 'Factor VII deficiency exclusively affects the extrinsic pathway (measured by PT), causing isolated PT prolongation without affecting PTT.', 'options' => [['label' => 'A', 'text' => 'Factor VIII', 'is_correct' => false], ['label' => 'B', 'text' => 'Factor VII', 'is_correct' => true], ['label' => 'C', 'text' => 'Factor IX', 'is_correct' => false], ['label' => 'D', 'text' => 'Factor XI', 'is_correct' => false]]],
                                ['question' => 'Reed-Sternberg cells are diagnostic of which malignancy?', 'explanation' => 'Reed-Sternberg cells (large binucleated cells with prominent nucleoli giving an "owl-eye" appearance) are the pathognomonic finding in Hodgkin lymphoma.', 'options' => [['label' => 'A', 'text' => 'Non-Hodgkin lymphoma', 'is_correct' => false], ['label' => 'B', 'text' => 'Hodgkin lymphoma', 'is_correct' => true], ['label' => 'C', 'text' => 'Chronic lymphocytic leukemia', 'is_correct' => false], ['label' => 'D', 'text' => 'Multiple myeloma', 'is_correct' => false]]],
                                ['question' => 'What is the Philadelphia chromosome associated with?', 'explanation' => 'The Philadelphia chromosome t(9;22) resulting in BCR-ABL fusion is the hallmark of chronic myeloid leukemia (CML), present in >95% of cases.', 'options' => [['label' => 'A', 'text' => 'Acute lymphoblastic leukemia only', 'is_correct' => false], ['label' => 'B', 'text' => 'Chronic myeloid leukemia', 'is_correct' => true], ['label' => 'C', 'text' => 'Burkitt lymphoma', 'is_correct' => false], ['label' => 'D', 'text' => 'Hairy cell leukemia', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Infectious Disease Board Review',
                        'lessons' => [
                            ['title' => 'HIV and Opportunistic Infections', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Antibiotic Stewardship Principles', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Tropical and Travel Medicine', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Infectious Disease Quiz',
                            'questions' => [
                                ['question' => 'At what CD4 count does Pneumocystis jirovecii pneumonia prophylaxis begin?', 'explanation' => 'PCP prophylaxis with trimethoprim-sulfamethoxazole should be started when CD4 count falls below 200 cells/microL in HIV patients.', 'options' => [['label' => 'A', 'text' => '<500 cells/microL', 'is_correct' => false], ['label' => 'B', 'text' => '<200 cells/microL', 'is_correct' => true], ['label' => 'C', 'text' => '<100 cells/microL', 'is_correct' => false], ['label' => 'D', 'text' => '<50 cells/microL', 'is_correct' => false]]],
                                ['question' => 'Which organism causes ring-enhancing lesions in HIV patients?', 'explanation' => 'Toxoplasma gondii classically causes multiple ring-enhancing brain lesions in HIV patients with CD4 <100, usually in the basal ganglia.', 'options' => [['label' => 'A', 'text' => 'Cryptococcus neoformans', 'is_correct' => false], ['label' => 'B', 'text' => 'Toxoplasma gondii', 'is_correct' => true], ['label' => 'C', 'text' => 'CMV', 'is_correct' => false], ['label' => 'D', 'text' => 'JC virus', 'is_correct' => false]]],
                                ['question' => 'What is the drug of choice for MRSA skin infections?', 'explanation' => 'For mild MRSA skin infections (abscesses), incision and drainage may suffice. For moderate infections, trimethoprim-sulfamethoxazole or doxycycline are first-line. Vancomycin is used for severe infections.', 'options' => [['label' => 'A', 'text' => 'Amoxicillin', 'is_correct' => false], ['label' => 'B', 'text' => 'Trimethoprim-sulfamethoxazole', 'is_correct' => true], ['label' => 'C', 'text' => 'Cephalexin', 'is_correct' => false], ['label' => 'D', 'text' => 'Ciprofloxacin', 'is_correct' => false]]],
                                ['question' => 'Fever with negative India ink and positive cryptococcal antigen in CSF indicates?', 'explanation' => 'Cryptococcal meningitis in HIV patients is diagnosed by CSF cryptococcal antigen (highly sensitive) and confirmed by India ink stain (less sensitive) and culture.', 'options' => [['label' => 'A', 'text' => 'Bacterial meningitis', 'is_correct' => false], ['label' => 'B', 'text' => 'Cryptococcal meningitis', 'is_correct' => true], ['label' => 'C', 'text' => 'Viral meningitis', 'is_correct' => false], ['label' => 'D', 'text' => 'TB meningitis', 'is_correct' => false]]],
                                ['question' => 'Which antibiotic class should be avoided with tendon disorders?', 'explanation' => 'Fluoroquinolones are associated with tendinopathy and tendon rupture, particularly the Achilles tendon, especially in elderly patients and those on corticosteroids.', 'options' => [['label' => 'A', 'text' => 'Penicillins', 'is_correct' => false], ['label' => 'B', 'text' => 'Fluoroquinolones', 'is_correct' => true], ['label' => 'C', 'text' => 'Macrolides', 'is_correct' => false], ['label' => 'D', 'text' => 'Cephalosporins', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Rheumatology and Nephrology Review',
                        'lessons' => [
                            ['title' => 'Systemic Lupus Erythematosus', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Rheumatoid Arthritis Management', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Glomerulonephritis Classification', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Acid-Base Disorders Review', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Rheumatology and Nephrology Quiz',
                            'questions' => [
                                ['question' => 'Which antibody is most specific for SLE?', 'explanation' => 'Anti-dsDNA antibodies are highly specific for SLE and correlate with disease activity and lupus nephritis. Anti-Smith antibodies are also highly specific but less sensitive.', 'options' => [['label' => 'A', 'text' => 'ANA', 'is_correct' => false], ['label' => 'B', 'text' => 'Anti-dsDNA', 'is_correct' => true], ['label' => 'C', 'text' => 'Anti-histone', 'is_correct' => false], ['label' => 'D', 'text' => 'Anti-Ro', 'is_correct' => false]]],
                                ['question' => 'What is the first-line DMARD for rheumatoid arthritis?', 'explanation' => 'Methotrexate is the first-line disease-modifying antirheumatic drug (DMARD) for RA and is considered the anchor drug in RA treatment.', 'options' => [['label' => 'A', 'text' => 'Hydroxychloroquine', 'is_correct' => false], ['label' => 'B', 'text' => 'Methotrexate', 'is_correct' => true], ['label' => 'C', 'text' => 'Sulfasalazine', 'is_correct' => false], ['label' => 'D', 'text' => 'Leflunomide', 'is_correct' => false]]],
                                ['question' => 'IgA nephropathy classically presents with?', 'explanation' => 'IgA nephropathy (Berger disease) classically presents with episodic gross hematuria occurring 1-2 days after an upper respiratory infection (synpharyngitic hematuria).', 'options' => [['label' => 'A', 'text' => 'Nephrotic syndrome', 'is_correct' => false], ['label' => 'B', 'text' => 'Gross hematuria after upper respiratory infection', 'is_correct' => true], ['label' => 'C', 'text' => 'Rapidly progressive renal failure', 'is_correct' => false], ['label' => 'D', 'text' => 'Chronic painless proteinuria', 'is_correct' => false]]],
                                ['question' => 'What acid-base disorder shows low pH, low HCO3, and low pCO2?', 'explanation' => 'Low pH indicates acidemia. Low HCO3 is the primary metabolic disorder. Low pCO2 represents appropriate respiratory compensation. This is metabolic acidosis with respiratory compensation.', 'options' => [['label' => 'A', 'text' => 'Respiratory acidosis', 'is_correct' => false], ['label' => 'B', 'text' => 'Metabolic acidosis with respiratory compensation', 'is_correct' => true], ['label' => 'C', 'text' => 'Respiratory alkalosis', 'is_correct' => false], ['label' => 'D', 'text' => 'Mixed metabolic and respiratory acidosis', 'is_correct' => false]]],
                                ['question' => 'Anti-CCP antibodies are most specific for which condition?', 'explanation' => 'Anti-cyclic citrullinated peptide (anti-CCP) antibodies have >95% specificity for rheumatoid arthritis and can be positive years before clinical disease onset.', 'options' => [['label' => 'A', 'text' => 'Systemic lupus erythematosus', 'is_correct' => false], ['label' => 'B', 'text' => 'Rheumatoid arthritis', 'is_correct' => true], ['label' => 'C', 'text' => 'Ankylosing spondylitis', 'is_correct' => false], ['label' => 'D', 'text' => 'Psoriatic arthritis', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 11
            [
                'title' => 'Clinical Dermatology Procedures',
                'instructor_email' => 'sara@spc-academy.com',
                'category_slug' => 'dermatology',
                'short_description' => 'Hands-on dermatological procedures including biopsy techniques, cryotherapy, and minor surgical procedures.',
                'description' => 'Dr. Sara El-Sayed guides you through essential dermatological procedures used in clinical practice. Learn biopsy techniques, cryotherapy, electrosurgery, and other office-based procedures with step-by-step demonstrations.',
                'price' => 1000,
                'original_price' => 1300,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Basic dermatology knowledge', 'Understanding of skin anatomy', 'Clinical setting access recommended'],
                'learning_outcomes' => ['Perform skin biopsy techniques', 'Apply cryotherapy correctly', 'Understand electrosurgery principles', 'Manage procedure complications'],
                'tags' => ['dermatology', 'procedures', 'biopsy', 'cryotherapy'],
                'modules' => [
                    [
                        'title' => 'Skin Biopsy Techniques',
                        'lessons' => [
                            ['title' => 'Introduction to Dermatological Procedures', 'type' => 'video', 'duration_minutes' => 20, 'is_free' => true],
                            ['title' => 'Punch Biopsy Technique', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Shave and Excisional Biopsy', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Biopsy Site Selection Guide', 'type' => 'reading', 'content' => 'Guide for selecting the optimal biopsy site and technique based on lesion type and clinical scenario.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Skin Biopsy Techniques Quiz',
                            'questions' => [
                                ['question' => 'Which biopsy technique is best for suspected melanoma?', 'explanation' => 'Excisional biopsy with narrow margins is the preferred technique for suspected melanoma to obtain complete lesion architecture and accurate Breslow depth measurement.', 'options' => [['label' => 'A', 'text' => 'Shave biopsy', 'is_correct' => false], ['label' => 'B', 'text' => 'Excisional biopsy', 'is_correct' => true], ['label' => 'C', 'text' => 'Curettage', 'is_correct' => false], ['label' => 'D', 'text' => 'Fine needle aspiration', 'is_correct' => false]]],
                                ['question' => 'What is the standard punch biopsy size for most diagnostic purposes?', 'explanation' => 'A 4mm punch biopsy is the standard size for most diagnostic biopsies, providing adequate tissue while minimizing the wound. 3mm may be used on cosmetically sensitive areas.', 'options' => [['label' => 'A', 'text' => '2 mm', 'is_correct' => false], ['label' => 'B', 'text' => '4 mm', 'is_correct' => true], ['label' => 'C', 'text' => '6 mm', 'is_correct' => false], ['label' => 'D', 'text' => '8 mm', 'is_correct' => false]]],
                                ['question' => 'Shave biopsy is contraindicated for which lesion?', 'explanation' => 'Shave biopsy should never be used for pigmented lesions suspicious for melanoma as it may transect the lesion and prevent accurate depth measurement.', 'options' => [['label' => 'A', 'text' => 'Seborrheic keratosis', 'is_correct' => false], ['label' => 'B', 'text' => 'Suspected melanoma', 'is_correct' => true], ['label' => 'C', 'text' => 'Skin tag', 'is_correct' => false], ['label' => 'D', 'text' => 'Wart', 'is_correct' => false]]],
                                ['question' => 'Which local anesthetic is most commonly used for skin biopsy?', 'explanation' => 'Lidocaine 1% with epinephrine is the most commonly used local anesthetic for dermatological procedures. Epinephrine provides hemostasis and prolongs anesthesia duration.', 'options' => [['label' => 'A', 'text' => 'Bupivacaine without epinephrine', 'is_correct' => false], ['label' => 'B', 'text' => 'Lidocaine 1% with epinephrine', 'is_correct' => true], ['label' => 'C', 'text' => 'Prilocaine 4%', 'is_correct' => false], ['label' => 'D', 'text' => 'Benzocaine topical only', 'is_correct' => false]]],
                                ['question' => 'After a punch biopsy, the wound is typically closed with?', 'explanation' => 'Punch biopsy wounds 4mm or larger are typically closed with 1-2 simple interrupted sutures to promote healing and minimize scarring.', 'options' => [['label' => 'A', 'text' => 'Left open to heal by secondary intention', 'is_correct' => false], ['label' => 'B', 'text' => 'Simple interrupted sutures', 'is_correct' => true], ['label' => 'C', 'text' => 'Staples', 'is_correct' => false], ['label' => 'D', 'text' => 'Tissue glue only', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Cryotherapy and Electrosurgery',
                        'lessons' => [
                            ['title' => 'Cryotherapy Principles and Techniques', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Electrosurgery and Cauterization', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Post-Procedure Care and Complications', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Cryotherapy and Electrosurgery Quiz',
                            'questions' => [
                                ['question' => 'What is the agent most commonly used in cryotherapy?', 'explanation' => 'Liquid nitrogen (-196 degrees Celsius) is the most commonly used cryogen in dermatological practice for treating warts, actinic keratoses, and other lesions.', 'options' => [['label' => 'A', 'text' => 'Dry ice', 'is_correct' => false], ['label' => 'B', 'text' => 'Liquid nitrogen', 'is_correct' => true], ['label' => 'C', 'text' => 'Carbon dioxide', 'is_correct' => false], ['label' => 'D', 'text' => 'Dimethyl ether propane', 'is_correct' => false]]],
                                ['question' => 'What is the recommended freeze time for common warts?', 'explanation' => 'Common warts typically require 10-30 seconds of freeze time with liquid nitrogen, using a double freeze-thaw cycle for better efficacy.', 'options' => [['label' => 'A', 'text' => '1-3 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '10-30 seconds', 'is_correct' => true], ['label' => 'C', 'text' => '60-90 seconds', 'is_correct' => false], ['label' => 'D', 'text' => '2-5 minutes', 'is_correct' => false]]],
                                ['question' => 'Which electrosurgery mode is used for coagulation?', 'explanation' => 'Fulguration/coagulation mode uses a damped waveform to achieve tissue coagulation and hemostasis with less cutting effect compared to cutting mode.', 'options' => [['label' => 'A', 'text' => 'Cut mode', 'is_correct' => false], ['label' => 'B', 'text' => 'Coagulation/fulguration mode', 'is_correct' => true], ['label' => 'C', 'text' => 'Blend mode', 'is_correct' => false], ['label' => 'D', 'text' => 'Bipolar mode only', 'is_correct' => false]]],
                                ['question' => 'What is the most common side effect of cryotherapy?', 'explanation' => 'Blister formation is the most common side effect of cryotherapy, occurring within 24 hours of treatment. It is expected and usually resolves on its own.', 'options' => [['label' => 'A', 'text' => 'Infection', 'is_correct' => false], ['label' => 'B', 'text' => 'Blister formation', 'is_correct' => true], ['label' => 'C', 'text' => 'Nerve damage', 'is_correct' => false], ['label' => 'D', 'text' => 'Deep scarring', 'is_correct' => false]]],
                                ['question' => 'Electrosurgery is contraindicated in patients with?', 'explanation' => 'Electrosurgery (monopolar) should be used with caution in patients with cardiac pacemakers, as electrical current may interfere with pacemaker function.', 'options' => [['label' => 'A', 'text' => 'Diabetes', 'is_correct' => false], ['label' => 'B', 'text' => 'Cardiac pacemakers', 'is_correct' => true], ['label' => 'C', 'text' => 'Hypertension', 'is_correct' => false], ['label' => 'D', 'text' => 'Allergies to local anesthetics', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chemical Peels and Cosmetic Procedures',
                        'lessons' => [
                            ['title' => 'Chemical Peel Types and Indications', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Intralesional Injection Techniques', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Wound Care After Dermatological Procedures', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Chemical Peels and Cosmetic Procedures Quiz',
                            'questions' => [
                                ['question' => 'Which chemical peel depth is achieved with glycolic acid 30-50%?', 'explanation' => 'Glycolic acid 30-50% produces a superficial chemical peel affecting the epidermis, suitable for mild photoaging, acne, and mild hyperpigmentation.', 'options' => [['label' => 'A', 'text' => 'Superficial peel', 'is_correct' => true], ['label' => 'B', 'text' => 'Medium-depth peel', 'is_correct' => false], ['label' => 'C', 'text' => 'Deep peel', 'is_correct' => false], ['label' => 'D', 'text' => 'No clinical effect at this concentration', 'is_correct' => false]]],
                                ['question' => 'What is commonly injected intralesionally for keloids?', 'explanation' => 'Triamcinolone acetonide (10-40 mg/mL) is the most commonly used intralesional corticosteroid for treating keloids and hypertrophic scars.', 'options' => [['label' => 'A', 'text' => 'Hyaluronidase', 'is_correct' => false], ['label' => 'B', 'text' => 'Triamcinolone acetonide', 'is_correct' => true], ['label' => 'C', 'text' => 'Botulinum toxin', 'is_correct' => false], ['label' => 'D', 'text' => 'Lidocaine', 'is_correct' => false]]],
                                ['question' => 'TCA peel at 35% concentration produces which depth of peel?', 'explanation' => 'TCA (trichloroacetic acid) at 35% concentration produces a medium-depth peel reaching the papillary dermis, suitable for moderate photodamage and acne scars.', 'options' => [['label' => 'A', 'text' => 'Superficial peel', 'is_correct' => false], ['label' => 'B', 'text' => 'Medium-depth peel', 'is_correct' => true], ['label' => 'C', 'text' => 'Deep peel', 'is_correct' => false], ['label' => 'D', 'text' => 'Very superficial peel', 'is_correct' => false]]],
                                ['question' => 'What is the Fitzpatrick skin type classification used for?', 'explanation' => 'The Fitzpatrick skin type classification (types I-VI) categorizes skin by its response to UV exposure and guides treatment decisions for procedures like chemical peels and laser therapy.', 'options' => [['label' => 'A', 'text' => 'Grading acne severity', 'is_correct' => false], ['label' => 'B', 'text' => 'Classifying skin response to UV and guiding procedure selection', 'is_correct' => true], ['label' => 'C', 'text' => 'Measuring skin hydration', 'is_correct' => false], ['label' => 'D', 'text' => 'Determining sunscreen SPF requirement', 'is_correct' => false]]],
                                ['question' => 'Post-procedure wound care for chemical peels includes?', 'explanation' => 'Post-peel care includes gentle cleansing, emollient application, strict sun avoidance, broad-spectrum sunscreen use, and avoiding picking or peeling skin prematurely.', 'options' => [['label' => 'A', 'text' => 'Immediate sun exposure to promote healing', 'is_correct' => false], ['label' => 'B', 'text' => 'Gentle cleansing, emollient, and strict sun protection', 'is_correct' => true], ['label' => 'C', 'text' => 'Scrubbing the treated area daily', 'is_correct' => false], ['label' => 'D', 'text' => 'Applying retinoids immediately after the peel', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 12
            [
                'title' => 'Neonatal Resuscitation Program',
                'instructor_email' => 'mona@spc-academy.com',
                'category_slug' => 'pediatrics',
                'short_description' => 'NRP-based neonatal resuscitation training covering delivery room management and stabilization of newborns.',
                'description' => 'Dr. Mona Ibrahim delivers a comprehensive neonatal resuscitation course based on NRP guidelines. Learn systematic approaches to newborn assessment, stabilization, and resuscitation in the delivery room.',
                'price' => 1300,
                'original_price' => 1690,
                'level' => 'intermediate',
                'is_bundle' => false,
                'is_featured' => false,
                'requirements' => ['Basic neonatal physiology', 'BLS certification', 'Delivery room experience helpful'],
                'learning_outcomes' => ['Apply NRP algorithm systematically', 'Perform neonatal resuscitation steps', 'Manage common delivery room emergencies', 'Assess newborn transition effectively'],
                'tags' => ['NRP', 'neonatal', 'resuscitation', 'newborn', 'delivery room'],
                'modules' => [
                    [
                        'title' => 'Initial Assessment and Stabilization',
                        'lessons' => [
                            ['title' => 'NRP Overview and Preparation', 'type' => 'video', 'duration_minutes' => 20, 'is_free' => true],
                            ['title' => 'Initial Steps of Newborn Care', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Assessment of Heart Rate and Breathing', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'NRP Algorithm Flowchart', 'type' => 'reading', 'content' => 'Step-by-step NRP algorithm flowchart with decision points and timing guidelines.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Initial Assessment Quiz',
                            'questions' => [
                                ['question' => 'What are the initial steps of neonatal resuscitation?', 'explanation' => 'The initial steps include: provide warmth, position and clear airway (if needed), dry and stimulate, and assess breathing and heart rate.', 'options' => [['label' => 'A', 'text' => 'Warm, position, dry, stimulate, and assess', 'is_correct' => true], ['label' => 'B', 'text' => 'Intubate, ventilate, and start compressions', 'is_correct' => false], ['label' => 'C', 'text' => 'Suction, administer oxygen, and monitor', 'is_correct' => false], ['label' => 'D', 'text' => 'Assess APGAR and start medications', 'is_correct' => false]]],
                                ['question' => 'What is the target heart rate that indicates adequate newborn transition?', 'explanation' => 'A heart rate >100 bpm indicates adequate cardiac function in the newborn. HR <100 bpm requires positive pressure ventilation.', 'options' => [['label' => 'A', 'text' => '>60 bpm', 'is_correct' => false], ['label' => 'B', 'text' => '>80 bpm', 'is_correct' => false], ['label' => 'C', 'text' => '>100 bpm', 'is_correct' => true], ['label' => 'D', 'text' => '>120 bpm', 'is_correct' => false]]],
                                ['question' => 'What is the recommended initial oxygen concentration for term newborn resuscitation?', 'explanation' => 'Current NRP guidelines recommend starting with 21% oxygen (room air) for term newborns and titrating based on pre-ductal pulse oximetry.', 'options' => [['label' => 'A', 'text' => '100% oxygen', 'is_correct' => false], ['label' => 'B', 'text' => '21% (room air)', 'is_correct' => true], ['label' => 'C', 'text' => '40% oxygen', 'is_correct' => false], ['label' => 'D', 'text' => '60% oxygen', 'is_correct' => false]]],
                                ['question' => 'How long should you assess breathing and heart rate before intervening?', 'explanation' => 'The "golden minute" concept emphasizes that initial steps and evaluation should be completed, and PPV initiated if needed, within the first 60 seconds of life.', 'options' => [['label' => 'A', 'text' => '30 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '60 seconds (the golden minute)', 'is_correct' => true], ['label' => 'C', 'text' => '2 minutes', 'is_correct' => false], ['label' => 'D', 'text' => '5 minutes', 'is_correct' => false]]],
                                ['question' => 'Where should the pulse oximeter probe be placed on a newborn?', 'explanation' => 'The pulse oximeter probe should be placed on the right hand or wrist (pre-ductal) to measure pre-ductal oxygen saturation in newborns.', 'options' => [['label' => 'A', 'text' => 'Left foot', 'is_correct' => false], ['label' => 'B', 'text' => 'Right hand or wrist (pre-ductal)', 'is_correct' => true], ['label' => 'C', 'text' => 'Either foot', 'is_correct' => false], ['label' => 'D', 'text' => 'Left hand', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Positive Pressure Ventilation and Intubation',
                        'lessons' => [
                            ['title' => 'Positive Pressure Ventilation Technique', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Neonatal Intubation', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Laryngeal Mask Airway in Neonates', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'PPV and Intubation Quiz',
                            'questions' => [
                                ['question' => 'What is the recommended PPV rate for neonatal resuscitation?', 'explanation' => 'PPV should be delivered at a rate of 40-60 breaths per minute in neonatal resuscitation, using the rhythm "breathe-two-three, breathe-two-three."', 'options' => [['label' => 'A', 'text' => '10-20 breaths per minute', 'is_correct' => false], ['label' => 'B', 'text' => '20-30 breaths per minute', 'is_correct' => false], ['label' => 'C', 'text' => '40-60 breaths per minute', 'is_correct' => true], ['label' => 'D', 'text' => '80-100 breaths per minute', 'is_correct' => false]]],
                                ['question' => 'MR SOPA is a mnemonic used for?', 'explanation' => 'MR SOPA corrective steps for ineffective PPV: Mask adjustment, Reposition airway, Suction mouth/nose, Open mouth, Pressure increase, Alternative airway.', 'options' => [['label' => 'A', 'text' => 'Steps for chest compressions', 'is_correct' => false], ['label' => 'B', 'text' => 'Corrective steps for ineffective PPV', 'is_correct' => true], ['label' => 'C', 'text' => 'Medication administration order', 'is_correct' => false], ['label' => 'D', 'text' => 'Post-resuscitation care checklist', 'is_correct' => false]]],
                                ['question' => 'What ETT size is appropriate for a term newborn?', 'explanation' => 'A 3.5 mm ETT is appropriate for term newborns (>3 kg). Size 3.0 is used for 2-3 kg, size 2.5 for 1-2 kg, and size 2.5 for <1 kg.', 'options' => [['label' => 'A', 'text' => '2.5 mm', 'is_correct' => false], ['label' => 'B', 'text' => '3.0 mm', 'is_correct' => false], ['label' => 'C', 'text' => '3.5 mm', 'is_correct' => true], ['label' => 'D', 'text' => '4.0 mm', 'is_correct' => false]]],
                                ['question' => 'When is intubation indicated in neonatal resuscitation?', 'explanation' => 'Intubation is indicated when PPV is ineffective or prolonged, when chest compressions are needed, for special circumstances like CDH, or for tracheal suctioning of thick meconium (if non-vigorous).', 'options' => [['label' => 'A', 'text' => 'For all newborns requiring resuscitation', 'is_correct' => false], ['label' => 'B', 'text' => 'When PPV with mask is ineffective or prolonged', 'is_correct' => true], ['label' => 'C', 'text' => 'Only after medications are given', 'is_correct' => false], ['label' => 'D', 'text' => 'Only for preterm infants', 'is_correct' => false]]],
                                ['question' => 'What peak inspiratory pressure should be used initially for term newborn PPV?', 'explanation' => 'Initial PIP of 20-25 cmH2O is recommended for term newborns. Pressure should be adjusted based on chest rise and heart rate response.', 'options' => [['label' => 'A', 'text' => '10-15 cmH2O', 'is_correct' => false], ['label' => 'B', 'text' => '20-25 cmH2O', 'is_correct' => true], ['label' => 'C', 'text' => '30-35 cmH2O', 'is_correct' => false], ['label' => 'D', 'text' => '40-45 cmH2O', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Chest Compressions and Medications',
                        'lessons' => [
                            ['title' => 'Neonatal Chest Compressions', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Epinephrine and Volume Expansion', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Special Considerations in NRP', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Chest Compressions and Medications Quiz',
                            'questions' => [
                                ['question' => 'What is the compression-to-ventilation ratio in neonatal resuscitation?', 'explanation' => 'The compression-to-ventilation ratio in NRP is 3:1 (3 compressions followed by 1 ventilation), providing 90 compressions and 30 breaths per minute.', 'options' => [['label' => 'A', 'text' => '15:2', 'is_correct' => false], ['label' => 'B', 'text' => '30:2', 'is_correct' => false], ['label' => 'C', 'text' => '3:1', 'is_correct' => true], ['label' => 'D', 'text' => '5:1', 'is_correct' => false]]],
                                ['question' => 'At what heart rate should chest compressions be initiated in a newborn?', 'explanation' => 'Chest compressions are initiated when heart rate remains <60 bpm despite 30 seconds of effective positive pressure ventilation with 100% oxygen.', 'options' => [['label' => 'A', 'text' => '<100 bpm', 'is_correct' => false], ['label' => 'B', 'text' => '<80 bpm', 'is_correct' => false], ['label' => 'C', 'text' => '<60 bpm', 'is_correct' => true], ['label' => 'D', 'text' => '<40 bpm', 'is_correct' => false]]],
                                ['question' => 'What is the dose of epinephrine for neonatal resuscitation via IV/UVC?', 'explanation' => 'IV/UVC epinephrine dose in NRP is 0.01-0.03 mg/kg (0.1-0.3 mL/kg of 1:10,000 concentration). ET dose is higher: 0.05-0.1 mg/kg.', 'options' => [['label' => 'A', 'text' => '0.001 mg/kg', 'is_correct' => false], ['label' => 'B', 'text' => '0.01-0.03 mg/kg', 'is_correct' => true], ['label' => 'C', 'text' => '0.1 mg/kg', 'is_correct' => false], ['label' => 'D', 'text' => '1 mg/kg', 'is_correct' => false]]],
                                ['question' => 'Which technique is preferred for neonatal chest compressions?', 'explanation' => 'The two-thumb encircling technique is preferred for neonatal chest compressions as it generates higher peak systolic and coronary perfusion pressures compared to the two-finger technique.', 'options' => [['label' => 'A', 'text' => 'Two-finger technique', 'is_correct' => false], ['label' => 'B', 'text' => 'Two-thumb encircling technique', 'is_correct' => true], ['label' => 'C', 'text' => 'One-hand technique', 'is_correct' => false], ['label' => 'D', 'text' => 'Heel of hand technique', 'is_correct' => false]]],
                                ['question' => 'Normal saline for volume expansion in neonates is given at what dose?', 'explanation' => 'Volume expansion with normal saline is given at 10 mL/kg over 5-10 minutes via UVC for suspected hypovolemia during neonatal resuscitation.', 'options' => [['label' => 'A', 'text' => '5 mL/kg', 'is_correct' => false], ['label' => 'B', 'text' => '10 mL/kg', 'is_correct' => true], ['label' => 'C', 'text' => '20 mL/kg', 'is_correct' => false], ['label' => 'D', 'text' => '30 mL/kg', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 13 - Bundle
            [
                'title' => 'Complete Cardiology Bundle',
                'instructor_email' => 'khaled@spc-academy.com',
                'category_slug' => 'cardiology',
                'short_description' => 'Complete cardiology training bundle combining ECG interpretation and ACLS courses at a discounted price.',
                'description' => 'Get the complete cardiology education package with this exclusive bundle. Includes ECG Interpretation Masterclass and Advanced Cardiac Life Support (ACLS) course. Save over 35% compared to buying courses individually.',
                'price' => 3500,
                'original_price' => 5600,
                'level' => 'intermediate',
                'is_bundle' => true,
                'is_featured' => false,
                'requirements' => ['Basic cardiac anatomy', 'BLS certification recommended', 'Clinical experience helpful'],
                'learning_outcomes' => ['Master ECG interpretation', 'Apply ACLS algorithms', 'Manage cardiac emergencies', 'Prepare for cardiology certifications'],
                'tags' => ['cardiology', 'bundle', 'ECG', 'ACLS'],
                'modules' => [
                    [
                        'title' => 'Bundle Overview and ECG Foundations',
                        'lessons' => [
                            ['title' => 'Complete Cardiology Bundle Welcome', 'type' => 'video', 'duration_minutes' => 15, 'is_free' => true],
                            ['title' => 'Cardiac Anatomy for ECG', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Electrophysiology Basics', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Bundle Study Plan', 'type' => 'reading', 'content' => 'Recommended study plan for completing both ECG and ACLS content within 8 weeks.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'ECG Foundations Quiz',
                            'questions' => [
                                ['question' => 'What is the intrinsic rate of the SA node?', 'explanation' => 'The SA node is the primary pacemaker of the heart with an intrinsic rate of 60-100 bpm. The AV node fires at 40-60 bpm, and the Purkinje fibers at 20-40 bpm.', 'options' => [['label' => 'A', 'text' => '20-40 bpm', 'is_correct' => false], ['label' => 'B', 'text' => '40-60 bpm', 'is_correct' => false], ['label' => 'C', 'text' => '60-100 bpm', 'is_correct' => true], ['label' => 'D', 'text' => '100-150 bpm', 'is_correct' => false]]],
                                ['question' => 'Which coronary artery supplies the SA node in most patients?', 'explanation' => 'The right coronary artery (RCA) supplies the SA node in approximately 55-60% of patients, while the left circumflex supplies it in the remaining 40-45%.', 'options' => [['label' => 'A', 'text' => 'Left anterior descending artery', 'is_correct' => false], ['label' => 'B', 'text' => 'Right coronary artery', 'is_correct' => true], ['label' => 'C', 'text' => 'Left circumflex artery', 'is_correct' => false], ['label' => 'D', 'text' => 'Left main coronary artery', 'is_correct' => false]]],
                                ['question' => 'The normal cardiac conduction sequence is?', 'explanation' => 'Normal cardiac conduction: SA node -> atria -> AV node -> Bundle of His -> bundle branches -> Purkinje fibers -> ventricles.', 'options' => [['label' => 'A', 'text' => 'SA node, AV node, Bundle of His, Purkinje fibers', 'is_correct' => true], ['label' => 'B', 'text' => 'AV node, SA node, Purkinje fibers, Bundle of His', 'is_correct' => false], ['label' => 'C', 'text' => 'Purkinje fibers, AV node, SA node, Bundle of His', 'is_correct' => false], ['label' => 'D', 'text' => 'Bundle of His, SA node, AV node, Purkinje fibers', 'is_correct' => false]]],
                                ['question' => 'What is the speed of standard ECG paper recording?', 'explanation' => 'Standard ECG paper speed is 25 mm/sec. At this speed, each small box (1 mm) = 0.04 sec, and each large box (5 mm) = 0.20 sec.', 'options' => [['label' => 'A', 'text' => '10 mm/sec', 'is_correct' => false], ['label' => 'B', 'text' => '25 mm/sec', 'is_correct' => true], ['label' => 'C', 'text' => '50 mm/sec', 'is_correct' => false], ['label' => 'D', 'text' => '100 mm/sec', 'is_correct' => false]]],
                                ['question' => 'What does each small box on ECG paper represent in time?', 'explanation' => 'At standard paper speed of 25 mm/sec, each small box (1 mm) represents 0.04 seconds (40 milliseconds).', 'options' => [['label' => 'A', 'text' => '0.02 seconds', 'is_correct' => false], ['label' => 'B', 'text' => '0.04 seconds', 'is_correct' => true], ['label' => 'C', 'text' => '0.10 seconds', 'is_correct' => false], ['label' => 'D', 'text' => '0.20 seconds', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Integrated Arrhythmia and Emergency Management',
                        'lessons' => [
                            ['title' => 'From ECG to Clinical Decision', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Integrating ACLS into Clinical Practice', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Mock ACLS Scenarios', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Integrated Management Quiz',
                            'questions' => [
                                ['question' => 'A patient presents with wide complex tachycardia and hemodynamic instability. What is the first intervention?', 'explanation' => 'Any unstable tachycardia with a pulse should be treated with immediate synchronized cardioversion, regardless of whether the rhythm is narrow or wide complex.', 'options' => [['label' => 'A', 'text' => 'Adenosine trial', 'is_correct' => false], ['label' => 'B', 'text' => 'Immediate synchronized cardioversion', 'is_correct' => true], ['label' => 'C', 'text' => 'Amiodarone infusion', 'is_correct' => false], ['label' => 'D', 'text' => 'Obtain 12-lead ECG first', 'is_correct' => false]]],
                                ['question' => 'In the ACLS algorithm, when should you consider reversible causes?', 'explanation' => 'Reversible causes (Hs and Ts) should be considered throughout the resuscitation, starting from the beginning and reassessed during each cycle of the algorithm.', 'options' => [['label' => 'A', 'text' => 'Only after 20 minutes of resuscitation', 'is_correct' => false], ['label' => 'B', 'text' => 'Throughout the entire resuscitation from the start', 'is_correct' => true], ['label' => 'C', 'text' => 'Only for non-shockable rhythms', 'is_correct' => false], ['label' => 'D', 'text' => 'Only after ROSC', 'is_correct' => false]]],
                                ['question' => 'A stable patient with monomorphic VT should receive?', 'explanation' => 'Stable monomorphic VT can be treated with IV amiodarone 150 mg over 10 minutes. Cardioversion is reserved for unstable patients or if drugs fail.', 'options' => [['label' => 'A', 'text' => 'Immediate defibrillation', 'is_correct' => false], ['label' => 'B', 'text' => 'IV amiodarone', 'is_correct' => true], ['label' => 'C', 'text' => 'IV adenosine', 'is_correct' => false], ['label' => 'D', 'text' => 'Vagal maneuvers', 'is_correct' => false]]],
                                ['question' => 'What rhythm check interval should be maintained during CPR?', 'explanation' => 'Rhythm should be checked every 2 minutes (after each CPR cycle), minimizing interruptions to chest compressions. Pulse checks should also occur at this time.', 'options' => [['label' => 'A', 'text' => 'Every 30 seconds', 'is_correct' => false], ['label' => 'B', 'text' => 'Every 2 minutes', 'is_correct' => true], ['label' => 'C', 'text' => 'Every 5 minutes', 'is_correct' => false], ['label' => 'D', 'text' => 'Continuously without pause', 'is_correct' => false]]],
                                ['question' => 'End-tidal CO2 (ETCO2) monitoring during CPR indicates?', 'explanation' => 'ETCO2 monitoring is the most reliable method for confirming ETT placement and monitoring CPR quality. A sudden rise in ETCO2 may indicate ROSC.', 'options' => [['label' => 'A', 'text' => 'Oxygen saturation only', 'is_correct' => false], ['label' => 'B', 'text' => 'CPR quality and may indicate ROSC', 'is_correct' => true], ['label' => 'C', 'text' => 'Blood glucose levels', 'is_correct' => false], ['label' => 'D', 'text' => 'Medication effectiveness', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Certification Exam Preparation',
                        'lessons' => [
                            ['title' => 'Practice ECG Strips Review', 'type' => 'video', 'duration_minutes' => 45],
                            ['title' => 'ACLS Megacode Practice Scenarios', 'type' => 'video', 'duration_minutes' => 40],
                            ['title' => 'Exam Tips and Common Pitfalls', 'type' => 'video', 'duration_minutes' => 20],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Certification Preparation Quiz',
                            'questions' => [
                                ['question' => 'During a megacode scenario, a patient in VF receives a shock and converts to organized rhythm. What is the next step?', 'explanation' => 'After a shock converts VF to an organized rhythm, immediately resume CPR for 2 minutes before checking the pulse, as even organized rhythms may not generate adequate perfusion initially.', 'options' => [['label' => 'A', 'text' => 'Immediately check pulse', 'is_correct' => false], ['label' => 'B', 'text' => 'Resume CPR for 2 minutes then check pulse', 'is_correct' => true], ['label' => 'C', 'text' => 'Administer amiodarone', 'is_correct' => false], ['label' => 'D', 'text' => 'Prepare for another shock', 'is_correct' => false]]],
                                ['question' => 'Which team role is responsible for monitoring CPR quality?', 'explanation' => 'The team leader is responsible for monitoring overall CPR quality, though a dedicated CPR coach may assist. The compressor role should provide high-quality compressions.', 'options' => [['label' => 'A', 'text' => 'Airway manager', 'is_correct' => false], ['label' => 'B', 'text' => 'Team leader', 'is_correct' => true], ['label' => 'C', 'text' => 'IV/medication administrator', 'is_correct' => false], ['label' => 'D', 'text' => 'Recorder', 'is_correct' => false]]],
                                ['question' => 'What is the compression fraction target during CPR?', 'explanation' => 'The compression fraction (percentage of time compressions are being performed) should be at least 60%, ideally >80%. Minimizing interruptions is key.', 'options' => [['label' => 'A', 'text' => '>40%', 'is_correct' => false], ['label' => 'B', 'text' => '>60%, ideally >80%', 'is_correct' => true], ['label' => 'C', 'text' => '>90%', 'is_correct' => false], ['label' => 'D', 'text' => '100% at all times', 'is_correct' => false]]],
                                ['question' => 'Capnography reading of <10 mmHg during CPR suggests?', 'explanation' => 'ETCO2 <10 mmHg during CPR may indicate poor quality compressions or low cardiac output. It should prompt reassessment of CPR technique and compressor fatigue.', 'options' => [['label' => 'A', 'text' => 'Adequate CPR quality', 'is_correct' => false], ['label' => 'B', 'text' => 'Poor CPR quality or low cardiac output', 'is_correct' => true], ['label' => 'C', 'text' => 'Imminent ROSC', 'is_correct' => false], ['label' => 'D', 'text' => 'Hyperventilation', 'is_correct' => false]]],
                                ['question' => 'Effective team communication during resuscitation uses?', 'explanation' => 'Closed-loop communication (sender delivers message, receiver acknowledges, sender confirms) is the standard for effective team communication during resuscitation to prevent errors.', 'options' => [['label' => 'A', 'text' => 'Open discussion among all team members', 'is_correct' => false], ['label' => 'B', 'text' => 'Closed-loop communication', 'is_correct' => true], ['label' => 'C', 'text' => 'Written orders only', 'is_correct' => false], ['label' => 'D', 'text' => 'Hand signals', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 14 - Bundle
            [
                'title' => 'Surgery Masterclass Bundle',
                'instructor_email' => 'omar@spc-academy.com',
                'category_slug' => 'surgery',
                'short_description' => 'Complete surgical education bundle combining Surgical Skills Fundamentals and General Surgery Case Studies.',
                'description' => 'The ultimate surgical education package combining hands-on skills training with advanced case-based learning. Includes Surgical Skills Fundamentals and General Surgery Case Studies at a significant discount.',
                'price' => 4000,
                'original_price' => 6700,
                'level' => 'intermediate',
                'is_bundle' => true,
                'is_featured' => false,
                'requirements' => ['Medical student or above', 'Basic anatomy knowledge', 'Surgical rotation experience helpful'],
                'learning_outcomes' => ['Master fundamental surgical techniques', 'Analyze complex surgical cases', 'Develop operative decision-making skills', 'Prepare for surgical examinations'],
                'tags' => ['surgery', 'bundle', 'surgical skills', 'case studies'],
                'modules' => [
                    [
                        'title' => 'Surgical Foundations Review',
                        'lessons' => [
                            ['title' => 'Surgery Bundle Welcome and Overview', 'type' => 'video', 'duration_minutes' => 15, 'is_free' => true],
                            ['title' => 'Surgical Anatomy Essentials', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Pre-operative Assessment', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Surgical Safety Checklist', 'type' => 'reading', 'content' => 'WHO Surgical Safety Checklist overview and its application in clinical practice.'],
                            ['title' => 'Module 1 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Surgical Foundations Quiz',
                            'questions' => [
                                ['question' => 'What is the purpose of the WHO Surgical Safety Checklist?', 'explanation' => 'The WHO Surgical Safety Checklist reduces surgical complications and mortality by ensuring critical safety steps are completed at three phases: Sign In, Time Out, and Sign Out.', 'options' => [['label' => 'A', 'text' => 'To speed up surgical procedures', 'is_correct' => false], ['label' => 'B', 'text' => 'To reduce surgical complications through systematic safety checks', 'is_correct' => true], ['label' => 'C', 'text' => 'To replace pre-operative assessment', 'is_correct' => false], ['label' => 'D', 'text' => 'To document operative findings', 'is_correct' => false]]],
                                ['question' => 'What ASA classification indicates a patient with severe systemic disease?', 'explanation' => 'ASA III indicates a patient with severe systemic disease (e.g., poorly controlled DM, morbid obesity, active hepatitis). ASA I = healthy, ASA II = mild systemic disease.', 'options' => [['label' => 'A', 'text' => 'ASA I', 'is_correct' => false], ['label' => 'B', 'text' => 'ASA II', 'is_correct' => false], ['label' => 'C', 'text' => 'ASA III', 'is_correct' => true], ['label' => 'D', 'text' => 'ASA IV', 'is_correct' => false]]],
                                ['question' => 'Which pre-operative lab test is essential before major abdominal surgery?', 'explanation' => 'CBC, BMP (including renal function), coagulation profile, blood type and crossmatch are essential labs. Specific tests depend on the procedure and patient comorbidities.', 'options' => [['label' => 'A', 'text' => 'Thyroid function tests', 'is_correct' => false], ['label' => 'B', 'text' => 'CBC, BMP, coagulation profile, and type & crossmatch', 'is_correct' => true], ['label' => 'C', 'text' => 'Lipid panel', 'is_correct' => false], ['label' => 'D', 'text' => 'Vitamin D level', 'is_correct' => false]]],
                                ['question' => 'When should antibiotic prophylaxis be administered for surgery?', 'explanation' => 'Surgical antibiotic prophylaxis should be given within 60 minutes before incision (120 minutes for vancomycin/fluoroquinolones) to ensure adequate tissue levels.', 'options' => [['label' => 'A', 'text' => 'Day before surgery', 'is_correct' => false], ['label' => 'B', 'text' => 'Within 60 minutes before incision', 'is_correct' => true], ['label' => 'C', 'text' => 'After incision is made', 'is_correct' => false], ['label' => 'D', 'text' => 'Post-operatively in the recovery room', 'is_correct' => false]]],
                                ['question' => 'DVT prophylaxis in surgical patients should include?', 'explanation' => 'DVT prophylaxis includes mechanical methods (compression stockings, pneumatic devices) and pharmacological agents (LMWH or unfractionated heparin) based on risk stratification.', 'options' => [['label' => 'A', 'text' => 'Aspirin only', 'is_correct' => false], ['label' => 'B', 'text' => 'Mechanical and pharmacological prophylaxis based on risk', 'is_correct' => true], ['label' => 'C', 'text' => 'Early ambulation only', 'is_correct' => false], ['label' => 'D', 'text' => 'Warfarin started day before surgery', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Advanced Operative Techniques',
                        'lessons' => [
                            ['title' => 'Laparoscopic Surgery Principles', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Hemostasis and Energy Devices', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Anastomotic Techniques', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Module 2 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Advanced Operative Techniques Quiz',
                            'questions' => [
                                ['question' => 'What is the standard pneumoperitoneum pressure for laparoscopic surgery?', 'explanation' => 'Standard intra-abdominal pressure for laparoscopic surgery is 12-15 mmHg CO2. Higher pressures increase risk of complications.', 'options' => [['label' => 'A', 'text' => '5-8 mmHg', 'is_correct' => false], ['label' => 'B', 'text' => '12-15 mmHg', 'is_correct' => true], ['label' => 'C', 'text' => '20-25 mmHg', 'is_correct' => false], ['label' => 'D', 'text' => '30-35 mmHg', 'is_correct' => false]]],
                                ['question' => 'Calot\'s triangle is bounded by which structures?', 'explanation' => 'Calot\'s triangle (hepatobiliary triangle) is bounded by the cystic duct, common hepatic duct, and inferior edge of the liver. The cystic artery runs within this triangle.', 'options' => [['label' => 'A', 'text' => 'Cystic duct, common hepatic duct, and liver edge', 'is_correct' => true], ['label' => 'B', 'text' => 'Common bile duct, portal vein, and liver edge', 'is_correct' => false], ['label' => 'C', 'text' => 'Gallbladder, duodenum, and liver', 'is_correct' => false], ['label' => 'D', 'text' => 'Hepatic artery, portal vein, and bile duct', 'is_correct' => false]]],
                                ['question' => 'What principle must be achieved before clipping structures in laparoscopic cholecystectomy?', 'explanation' => 'The Critical View of Safety (CVS) must be achieved before clipping or dividing any structures, ensuring only two structures (cystic duct and artery) enter the gallbladder.', 'options' => [['label' => 'A', 'text' => 'Intraoperative cholangiogram', 'is_correct' => false], ['label' => 'B', 'text' => 'Critical View of Safety', 'is_correct' => true], ['label' => 'C', 'text' => 'Complete mobilization of gallbladder', 'is_correct' => false], ['label' => 'D', 'text' => 'Identification of common bile duct', 'is_correct' => false]]],
                                ['question' => 'Which energy device uses ultrasonic vibration to cut and coagulate?', 'explanation' => 'Harmonic scalpel uses ultrasonic energy (55,500 Hz vibrations) to simultaneously cut and coagulate tissue with minimal lateral thermal spread.', 'options' => [['label' => 'A', 'text' => 'Monopolar cautery', 'is_correct' => false], ['label' => 'B', 'text' => 'Harmonic scalpel', 'is_correct' => true], ['label' => 'C', 'text' => 'Bipolar forceps', 'is_correct' => false], ['label' => 'D', 'text' => 'Argon beam coagulator', 'is_correct' => false]]],
                                ['question' => 'A hand-sewn bowel anastomosis typically uses which suture technique?', 'explanation' => 'Hand-sewn bowel anastomosis commonly uses a two-layer technique: inner full-thickness running absorbable suture (e.g., Vicryl) and outer interrupted seromuscular silk sutures.', 'options' => [['label' => 'A', 'text' => 'Simple interrupted non-absorbable sutures only', 'is_correct' => false], ['label' => 'B', 'text' => 'Two-layer: inner running absorbable, outer interrupted seromuscular', 'is_correct' => true], ['label' => 'C', 'text' => 'Stapled only, never hand-sewn', 'is_correct' => false], ['label' => 'D', 'text' => 'Single-layer mattress sutures', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Post-Operative Care and Complications',
                        'lessons' => [
                            ['title' => 'Post-Operative Monitoring and Care', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Surgical Site Infections', 'type' => 'video', 'duration_minutes' => 25],
                            ['title' => 'Post-Operative Complications Management', 'type' => 'video', 'duration_minutes' => 35],
                            ['title' => 'Fluid and Electrolyte Management', 'type' => 'video', 'duration_minutes' => 30],
                            ['title' => 'Module 3 Quiz', 'type' => 'quiz'],
                        ],
                        'quiz' => [
                            'title' => 'Post-Operative Care Quiz',
                            'questions' => [
                                ['question' => 'When does a wound infection typically present after surgery?', 'explanation' => 'Surgical site infections (SSI) typically present 5-7 days postoperatively with wound erythema, warmth, swelling, pain, and purulent drainage. Early (<48h) infections suggest aggressive organisms.', 'options' => [['label' => 'A', 'text' => 'Within 6 hours', 'is_correct' => false], ['label' => 'B', 'text' => '5-7 days post-operatively', 'is_correct' => true], ['label' => 'C', 'text' => '2-3 weeks post-operatively', 'is_correct' => false], ['label' => 'D', 'text' => '4-6 weeks post-operatively', 'is_correct' => false]]],
                                ['question' => 'Post-operative fever on day 1 is most commonly due to?', 'explanation' => 'Using the 5 Ws mnemonic: Wind (day 1-2) = atelectasis. Water (day 3) = UTI. Wound (day 5-7) = SSI. Walking (day 4-6) = DVT. Wonder drugs (any time) = drug fever.', 'options' => [['label' => 'A', 'text' => 'Surgical site infection', 'is_correct' => false], ['label' => 'B', 'text' => 'Atelectasis', 'is_correct' => true], ['label' => 'C', 'text' => 'Urinary tract infection', 'is_correct' => false], ['label' => 'D', 'text' => 'Deep vein thrombosis', 'is_correct' => false]]],
                                ['question' => 'What is the initial management of anastomotic leak?', 'explanation' => 'Anastomotic leak requires NPO (bowel rest), IV antibiotics, IV fluids, and imaging. Surgical intervention (drainage, diversion, or revision) depends on clinical status and severity.', 'options' => [['label' => 'A', 'text' => 'Observation with oral antibiotics', 'is_correct' => false], ['label' => 'B', 'text' => 'NPO, IV antibiotics, IV fluids, imaging, and possible surgical intervention', 'is_correct' => true], ['label' => 'C', 'text' => 'Increase oral intake to test anastomosis', 'is_correct' => false], ['label' => 'D', 'text' => 'Immediate discharge with outpatient follow-up', 'is_correct' => false]]],
                                ['question' => 'What fluid is used for maintenance IV therapy in post-operative patients?', 'explanation' => 'Balanced crystalloids (Ringer lactate or Plasmalyte) are preferred over normal saline for maintenance fluids as they have a more physiological electrolyte composition and reduce hyperchloremic acidosis.', 'options' => [['label' => 'A', 'text' => 'D5W only', 'is_correct' => false], ['label' => 'B', 'text' => 'Balanced crystalloids (Ringer lactate or Plasmalyte)', 'is_correct' => true], ['label' => 'C', 'text' => 'Colloids in all patients', 'is_correct' => false], ['label' => 'D', 'text' => 'Hypertonic saline', 'is_correct' => false]]],
                                ['question' => 'Post-operative ileus typically resolves within?', 'explanation' => 'Post-operative ileus typically resolves within 3-5 days after abdominal surgery. Small bowel recovers first (24h), stomach next (24-48h), and colon last (48-72h).', 'options' => [['label' => 'A', 'text' => '6-12 hours', 'is_correct' => false], ['label' => 'B', 'text' => '3-5 days', 'is_correct' => true], ['label' => 'C', 'text' => '7-10 days', 'is_correct' => false], ['label' => 'D', 'text' => '2-3 weeks', 'is_correct' => false]]],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
