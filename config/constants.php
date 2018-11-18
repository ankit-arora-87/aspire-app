<?php
return [
    'loanTypes' => [
        [
            'type' => 'PERSONAL_LOAN',
            'amount' => 'Amount withdrawal upto S$100000 yearly',
            'repayment_duration' => 'Monthly repayment durations (1 - 12 months)',
            'interest_rate' => '1.5%'
        ],
        [
            'type' => 'BUSINESS_LOAN',
            'amount' => 'Amount withdrawal upto S$500000 yearly',
            'repayment_duration' => 'Monthly repayment durations (1 - 12 months)',
            'interest_rate' => '2%'
        ],
    ],
    'loanTypesLimit' => [
        [
            'PERSONAL' => [
                'amount' => 100000,
                'repayment_duration_limit' => 12,
                'interest_rate' => 1.5
    
            ],
            'BUSINESS' => [
                'amount' => 500000,
                'repayment_duration_limit' => 12,
                'interest_rate' => 2
    
            ]

        ]
    ],
    'loans_dir' => 'loans/',
    'documentTypes' => [
        'PROOF_OF_RESIDENCE',
        'TAX_SALARY_DECLARATION'
    ],
    'loanStatus' => [
            'APPLIED' => 'Applied',
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            'INREVIEW' => 'In Review',
            'DOCUMENTPENDING' => 'Document Pending',
            'REPAYMENT' => 'Repayment',
            'PAID' => 'Paid'
    ],
    'repaymentType' => [
        'REPAYMENT' => 'Repayment',
        'PENALTY' => 'Penalty',
        'PROCESSING' => 'Processing'
]
];