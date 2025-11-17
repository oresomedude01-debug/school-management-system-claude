// UI Components for School Management System

// Header/Navigation Component
class Header extends Component {
    constructor(props) {
        super(props);
        this.state = {
            user: null
        };

        store.subscribe((state) => {
            this.setState({ user: state.user });
        });
    }

    handleLogout() {
        api.post('/auth/logout').then(() => {
            store.setState({ user: null, isAuthenticated: false });
            router.navigate('/login');
        });
    }

    render() {
        const { user } = this.state;

        return createElement('header', { className: 'header' },
            createElement('div', { className: 'container header-content' },
                createElement('h1', { className: 'logo' }, 'School Management System'),
                user && createElement('div', { className: 'user-menu' },
                    createElement('span', { className: 'user-name' }, user.name),
                    createElement('button', {
                        className: 'btn btn-sm btn-danger',
                        onClick: () => this.handleLogout()
                    }, 'Logout')
                )
            )
        );
    }
}

// Sidebar Navigation Component
class Sidebar extends Component {
    render() {
        const menuItems = [
            { path: '/dashboard', icon: '📊', label: 'Dashboard' },
            { path: '/students', icon: '👨‍🎓', label: 'Students' },
            { path: '/teachers', icon: '👨‍🏫', label: 'Teachers' },
            { path: '/classes', icon: '🏫', label: 'Classes' },
            { path: '/subjects', icon: '📚', label: 'Subjects' },
            { path: '/attendance', icon: '📝', label: 'Attendance' },
            { path: '/grades', icon: '📊', label: 'Grades' },
        ];

        return createElement('aside', { className: 'sidebar' },
            createElement('nav', { className: 'nav-menu' },
                ...menuItems.map(item =>
                    createElement('a', {
                        href: '#' + item.path,
                        className: 'nav-item',
                        onClick: (e) => {
                            e.preventDefault();
                            router.navigate(item.path);
                        }
                    },
                        createElement('span', { className: 'nav-icon' }, item.icon),
                        createElement('span', { className: 'nav-label' }, item.label)
                    )
                )
            )
        );
    }
}

// Dashboard Component
class Dashboard extends Component {
    constructor(props) {
        super(props);
        this.state = {
            stats: {},
            loading: true
        };
    }

    async componentDidMount() {
        try {
            const stats = await api.get('/dashboard/stats');
            this.setState({ stats, loading: false });
        } catch (error) {
            this.setState({ loading: false });
        }
    }

    render() {
        const { stats, loading } = this.state;

        const content = createElement('div', { className: 'content' },
            new Header().render(),
            createElement('div', { className: 'layout' },
                new Sidebar().render(),
                createElement('main', { className: 'main-content' },
                    createElement('h2', {}, 'Dashboard'),

                    loading ? createElement('div', { className: 'loading' }, 'Loading...') :
                        createElement('div', { className: 'stats-grid' },
                            this.createStatCard('Total Students', stats.total_students || 0, '👨‍🎓'),
                            this.createStatCard('Active Students', stats.active_students || 0, '✅'),
                            this.createStatCard('Total Teachers', stats.total_teachers || 0, '👨‍🏫'),
                            this.createStatCard('Total Classes', stats.total_classes || 0, '🏫'),
                            this.createStatCard('Present Today', stats.present_today || 0, '📝'),
                        )
                )
            )
        );

        setTimeout(() => this.componentDidMount(), 0);
        return content;
    }

    createStatCard(title, value, icon) {
        return createElement('div', { className: 'stat-card' },
            createElement('div', { className: 'stat-icon' }, icon),
            createElement('div', { className: 'stat-details' },
                createElement('h3', {}, value),
                createElement('p', {}, title)
            )
        );
    }
}

// Students List Component
class StudentsList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            students: [],
            loading: true,
            showForm: false
        };
    }

    async loadStudents() {
        try {
            const data = await api.get('/students');
            this.setState({ students: data.data || data, loading: false });
        } catch (error) {
            this.setState({ loading: false });
        }
    }

    async deleteStudent(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            try {
                await api.delete('/students/' + id);
                this.loadStudents();
            } catch (error) {
                alert('Failed to delete student');
            }
        }
    }

    render() {
        const { students, loading, showForm } = this.state;

        const content = createElement('div', { className: 'content' },
            new Header().render(),
            createElement('div', { className: 'layout' },
                new Sidebar().render(),
                createElement('main', { className: 'main-content' },
                    createElement('div', { className: 'page-header' },
                        createElement('h2', {}, 'Students'),
                        createElement('button', {
                            className: 'btn btn-primary',
                            onClick: () => this.setState({ showForm: !showForm })
                        }, showForm ? 'Cancel' : 'Add Student')
                    ),

                    showForm && new StudentForm({
                        onSuccess: () => {
                            this.setState({ showForm: false });
                            this.loadStudents();
                        }
                    }).render(),

                    loading ? createElement('div', { className: 'loading' }, 'Loading...') :
                        createElement('div', { className: 'table-container' },
                            createElement('table', { className: 'table' },
                                createElement('thead', {},
                                    createElement('tr', {},
                                        createElement('th', {}, 'Admission No'),
                                        createElement('th', {}, 'Name'),
                                        createElement('th', {}, 'Email'),
                                        createElement('th', {}, 'Class'),
                                        createElement('th', {}, 'Status'),
                                        createElement('th', {}, 'Actions')
                                    )
                                ),
                                createElement('tbody', {},
                                    students.length === 0 ?
                                        createElement('tr', {},
                                            createElement('td', { colspan: '6', style: { textAlign: 'center' } }, 'No students found')
                                        ) :
                                        students.map(student =>
                                            createElement('tr', {},
                                                createElement('td', {}, student.admission_number),
                                                createElement('td', {}, student.user?.name || 'N/A'),
                                                createElement('td', {}, student.user?.email || 'N/A'),
                                                createElement('td', {}, student.class?.name || 'Not Assigned'),
                                                createElement('td', {},
                                                    createElement('span', {
                                                        className: `badge badge-${student.status === 'active' ? 'success' : 'secondary'}`
                                                    }, student.status)
                                                ),
                                                createElement('td', {},
                                                    createElement('button', {
                                                        className: 'btn btn-sm btn-danger',
                                                        onClick: () => this.deleteStudent(student.id)
                                                    }, 'Delete')
                                                )
                                            )
                                        )
                                )
                            )
                        )
                )
            )
        );

        setTimeout(() => {
            if (this.state.loading) {
                this.loadStudents();
            }
        }, 0);

        return content;
    }
}

// Student Form Component
class StudentForm extends Component {
    constructor(props) {
        super(props);
        this.state = {
            formData: {
                name: '',
                email: '',
                password: '',
                phone: '',
                address: '',
                admission_number: '',
                admission_date: new Date().toISOString().split('T')[0],
                date_of_birth: '',
                gender: 'male',
                parent_name: '',
                parent_phone: ''
            },
            classes: [],
            loading: false
        };
    }

    async loadClasses() {
        try {
            const data = await api.get('/classes');
            this.setState({ classes: data });
        } catch (error) {
            console.error('Failed to load classes');
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        this.setState({ loading: true });

        api.post('/students', this.state.formData)
            .then(() => {
                alert('Student added successfully!');
                this.props.onSuccess && this.props.onSuccess();
            })
            .catch(error => {
                alert('Failed to add student: ' + error.message);
                this.setState({ loading: false });
            });
    }

    handleChange(field, value) {
        this.setState({
            formData: { ...this.state.formData, [field]: value }
        });
    }

    render() {
        const { formData, classes, loading } = this.state;

        const content = createElement('div', { className: 'form-container' },
            createElement('h3', {}, 'Add New Student'),
            createElement('form', {
                className: 'form',
                onSubmit: (e) => this.handleSubmit(e)
            },
                createElement('div', { className: 'form-row' },
                    this.createInput('Name', 'name', formData.name, 'text', true),
                    this.createInput('Email', 'email', formData.email, 'email', true)
                ),
                createElement('div', { className: 'form-row' },
                    this.createInput('Password', 'password', formData.password, 'password', true),
                    this.createInput('Admission Number', 'admission_number', formData.admission_number, 'text', true)
                ),
                createElement('div', { className: 'form-row' },
                    this.createInput('Date of Birth', 'date_of_birth', formData.date_of_birth, 'date', true),
                    this.createInput('Admission Date', 'admission_date', formData.admission_date, 'date', true)
                ),
                createElement('div', { className: 'form-row' },
                    this.createSelect('Gender', 'gender', formData.gender, [
                        { value: 'male', label: 'Male' },
                        { value: 'female', label: 'Female' },
                        { value: 'other', label: 'Other' }
                    ]),
                    this.createInput('Phone', 'phone', formData.phone, 'tel')
                ),
                createElement('div', { className: 'form-row' },
                    this.createInput('Parent Name', 'parent_name', formData.parent_name, 'text', true),
                    this.createInput('Parent Phone', 'parent_phone', formData.parent_phone, 'tel', true)
                ),
                this.createTextarea('Address', 'address', formData.address),

                createElement('button', {
                    type: 'submit',
                    className: 'btn btn-primary',
                    disabled: loading
                }, loading ? 'Saving...' : 'Save Student')
            )
        );

        setTimeout(() => this.loadClasses(), 0);
        return content;
    }

    createInput(label, name, value, type = 'text', required = false) {
        return createElement('div', { className: 'form-group' },
            createElement('label', {}, label + (required ? ' *' : '')),
            createElement('input', {
                type,
                className: 'form-control',
                value: value || '',
                required,
                onInput: (e) => this.handleChange(name, e.target.value)
            })
        );
    }

    createSelect(label, name, value, options) {
        return createElement('div', { className: 'form-group' },
            createElement('label', {}, label),
            createElement('select', {
                className: 'form-control',
                value: value || '',
                onChange: (e) => this.handleChange(name, e.target.value)
            },
                ...options.map(opt =>
                    createElement('option', { value: opt.value }, opt.label)
                )
            )
        );
    }

    createTextarea(label, name, value) {
        return createElement('div', { className: 'form-group full-width' },
            createElement('label', {}, label),
            createElement('textarea', {
                className: 'form-control',
                value: value || '',
                rows: 3,
                onInput: (e) => this.handleChange(name, e.target.value)
            })
        );
    }
}

// Login Component
class Login extends Component {
    constructor(props) {
        super(props);
        this.state = {
            email: '',
            password: '',
            loading: false,
            error: ''
        };
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.setState({ loading: true, error: '' });

        try {
            const response = await api.post('/auth/login', {
                email: this.state.email,
                password: this.state.password
            });

            store.setState({
                user: response.user,
                isAuthenticated: true
            });

            router.navigate('/dashboard');
        } catch (error) {
            this.setState({
                error: error.message || 'Invalid credentials',
                loading: false
            });
        }
    }

    render() {
        const { email, password, loading, error } = this.state;

        return createElement('div', { className: 'login-container' },
            createElement('div', { className: 'login-box' },
                createElement('h1', { className: 'login-title' }, 'School Management System'),
                createElement('h2', {}, 'Login'),

                error && createElement('div', { className: 'alert alert-danger' }, error),

                createElement('form', {
                    className: 'form',
                    onSubmit: (e) => this.handleSubmit(e)
                },
                    createElement('div', { className: 'form-group' },
                        createElement('label', {}, 'Email'),
                        createElement('input', {
                            type: 'email',
                            className: 'form-control',
                            value: email,
                            required: true,
                            onInput: (e) => this.setState({ email: e.target.value })
                        })
                    ),
                    createElement('div', { className: 'form-group' },
                        createElement('label', {}, 'Password'),
                        createElement('input', {
                            type: 'password',
                            className: 'form-control',
                            value: password,
                            required: true,
                            onInput: (e) => this.setState({ password: e.target.value })
                        })
                    ),
                    createElement('button', {
                        type: 'submit',
                        className: 'btn btn-primary btn-block',
                        disabled: loading
                    }, loading ? 'Logging in...' : 'Login')
                )
            )
        );
    }
}
